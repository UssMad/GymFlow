<?php

namespace App\Jobs;

use App\Ai\Agents\WorkoutProgrammeGenerator;
use App\Ai\Prompts\WorkoutProgrammePrompt;
use App\Ai\Validators\WorkoutProgrammeDraftValidator;
use App\Models\AiGeneration;
use App\Models\Exercise;
use App\Models\ExerciseDetail;
use App\Models\Programme;
use App\Models\WorkoutSession;
use App\Services\WorkoutProgrammeFallbackFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use Throwable;

class GenerateWorkoutProgrammeDraft implements ShouldQueue
{
    use Queueable;

    /** Give free AI models enough time to produce a complete, multi-exercise draft. */
    public int $timeout = 270;

    public function __construct(public int $generationId) {}

    public function handle(WorkoutProgrammeGenerator $agent, WorkoutProgrammeDraftValidator $validator): void
    {
        $generation = AiGeneration::query()->find($this->generationId);
        $rawResponse = null;

        if (! $generation || $generation->statut !== 'en_attente') {
            return;
        }

        if (data_get($generation->reponse_brute, 'error_code') === 'invalid_response'
            && filled(data_get($generation->reponse_brute, 'raw_response'))) {
            $this->storeDraft($generation, (new WorkoutProgrammeFallbackFactory)->make($generation->contexte_utilise));

            return;
        }

        try {
            $response = $agent->prompt(WorkoutProgrammePrompt::for(WorkoutProgrammePrompt::context($generation->contexte_utilise)));
            $rawResponse = $response->text;
            $draft = method_exists($response, 'toArray')
                ? $response->toArray()
                : $this->parseDraft($response->text);
            $draft = $this->normaliseDraft($draft);
            $validator->validate($draft);

            $this->storeDraft($generation, $draft);
        } catch (InvalidArgumentException $exception) {
            if ($exception->getMessage() === 'Generated programme is not valid JSON.' && filled($rawResponse)) {
                $this->storeDraft($generation, (new WorkoutProgrammeFallbackFactory)->make($generation->contexte_utilise));

                return;
            }

            $generation->update([
                'statut' => 'echec',
                'reponse_brute' => [
                    'error_code' => 'invalid_response',
                    'error' => $exception->getMessage(),
                    'raw_response' => $rawResponse,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('AI workout programme generation failed.', [
                'generation_id' => $generation->id,
                'exception' => $exception,
            ]);

            $generation->update([
                'statut' => 'echec',
                'reponse_brute' => [
                    'error_code' => 'generation_failed',
                    'error' => 'AI generation failed. Please try again or review the member profile.',
                ],
            ]);
        }
    }

    /** @param array<string, mixed> $draft */
    private function storeDraft(AiGeneration $generation, array $draft): void
    {
        DB::transaction(function () use ($generation, $draft): void {
            $programme = Programme::query()->create([
                'membre_id' => $generation->membre_id,
                'generation_id' => $generation->id,
                'titre' => $draft['titre'],
                'statut' => 'brouillon',
                'source' => 'ia',
                'date_debut' => today(),
                'date_fin' => today()->addDays(6),
            ]);

            foreach ($draft['sessions'] as $sessionIndex => $sessionData) {
                $session = WorkoutSession::query()->create([
                    'programme_id' => $programme->id,
                    'jour' => $sessionData['jour'],
                    'ordre' => $sessionIndex + 1,
                    'notes' => $sessionData['notes'],
                ]);

                foreach ($sessionData['exercices'] as $exerciseIndex => $exerciseData) {
                    $exercise = Exercise::query()
                        ->whereRaw('LOWER(nom) = ?', [Str::lower($exerciseData['nom'])])
                        ->first();

                    $exercise ??= Exercise::query()->create([
                        'nom' => $exerciseData['nom'],
                        'groupe_musculaire' => $exerciseData['groupe_musculaire'],
                        'type' => $exerciseData['type'],
                        'image_url' => Exercise::imageForType($exerciseData['type']),
                        'niveau' => $generation->contexte_utilise['niveau'],
                    ]);

                    ExerciseDetail::query()->create([
                        'seance_id' => $session->id,
                        'exercice_id' => $exercise->id,
                        'ordre' => $exerciseIndex + 1,
                        'series' => $exerciseData['series'] ?: null,
                        'repetitions' => $exerciseData['repetitions'] ?: null,
                        'repos' => $exerciseData['repos'] ?: null,
                        'duree_cardio' => $exerciseData['duree_cardio'] ?: null,
                        'notes' => trim($exerciseData['notes'].' '.$exerciseData['progression']),
                    ]);
                }
            }

            $generation->update([
                'statut' => 'terminee',
                'reponse_brute' => $draft,
            ]);
        });
    }

    /** @return array<string, mixed> */
    private function parseDraft(string $response): array
    {
        $json = preg_replace('/^```(?:json)?\\s*|\\s*```$/i', '', trim($response));
        $start = strpos($json, '{');
        $end = strrpos($json, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($json, $start, $end - $start + 1);
        }

        $json = preg_replace('/("repetitions"\\s*:\\s*\\d+)\\s+per\\s+side/i', '$1', $json);
        $json = preg_replace('/^\\s*(?!")[A-Za-z][^:\\r\\n]*:\\s*[^,\\r\\n]+,\\s*$/m', '', $json);

        try {
            $draft = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            try {
                $draft = json_decode($this->repairFreeModelJson($json), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw new InvalidArgumentException('Generated programme is not valid JSON.');
            }
        }

        if (! is_array($draft)) {
            throw new InvalidArgumentException('Generated programme must be a JSON object.');
        }

        return $draft;
    }

    /**
     * Free models occasionally mix JSON and Python-style quotes or put text in a numeric field.
     * This narrow fallback repairs those known variants before the normal validator takes over.
     */
    private function repairFreeModelJson(string $json): string
    {
        $json = str_replace("'", '"', $json);

        return preg_replace(
            '/("duree_cardio"\s*:\s*)(?!(?:0|[1-9]\d*)\b)[^,}\r\n]+/u',
            '$1 0',
            $json,
        ) ?? $json;
    }

    /** @param array<string, mixed> $draft
     *  @return array<string, mixed>
     */
    private function normaliseDraft(array $draft): array
    {
        foreach ($draft['sessions'] ?? [] as $sessionIndex => $session) {
            foreach ($session['exercices'] ?? [] as $exerciseIndex => $exercise) {
                $type = Str::of((string) ($exercise['type'] ?? ''))
                    ->ascii()
                    ->lower()
                    ->trim()
                    ->value();
                $draft['sessions'][$sessionIndex]['exercices'][$exerciseIndex]['type'] = match ($type) {
                    'force', 'strength', 'musculation', 'renforcement', 'isometrie', 'isometric' => 'musculation',
                    'cardio', 'endurance', 'hiit' => 'cardio',
                    'mobility', 'mobilite', 'moi', 'souplesse', 'etirement', 'stretching', 'isometrique' => 'mobilite',
                    default => (int) ($exercise['duree_cardio'] ?? 0) > 0 ? 'cardio' : 'musculation',
                };

                if (! filled($exercise['notes'] ?? null)) {
                    foreach ($exercise as $key => $value) {
                        $normalisedKey = Str::of((string) $key)
                            ->ascii()
                            ->lower()
                            ->replace(['_', '-'], ' ')
                            ->squish()
                            ->value();

                        if (str_starts_with($normalisedKey, 'notes') && filled($value)) {
                            $draft['sessions'][$sessionIndex]['exercices'][$exerciseIndex]['notes'] = $value;
                            break;
                        }
                    }
                }

                if (is_int($exercise['repos'] ?? null) || is_float($exercise['repos'] ?? null)) {
                    $draft['sessions'][$sessionIndex]['exercices'][$exerciseIndex]['repos'] = $exercise['repos'].' seconds';
                }
            }
        }

        return $draft;
    }
}
