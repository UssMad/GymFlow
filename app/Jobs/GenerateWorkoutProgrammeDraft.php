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
    public int $timeout = 210;

    public function __construct(public int $generationId) {}

    public function handle(WorkoutProgrammeGenerator $agent, WorkoutProgrammeDraftValidator $validator): void
    {
        $generation = AiGeneration::query()->find($this->generationId);
        $rawResponse = null;

        if (! $generation || $generation->statut !== 'en_attente') {
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
        } catch (InvalidArgumentException $exception) {
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
            throw new InvalidArgumentException('Generated programme is not valid JSON.');
        }

        if (! is_array($draft)) {
            throw new InvalidArgumentException('Generated programme must be a JSON object.');
        }

        return $draft;
    }

    /** @param array<string, mixed> $draft
     *  @return array<string, mixed>
     */
    private function normaliseDraft(array $draft): array
    {
        foreach ($draft['sessions'] ?? [] as $sessionIndex => $session) {
            foreach ($session['exercices'] ?? [] as $exerciseIndex => $exercise) {
                $type = Str::lower((string) ($exercise['type'] ?? ''));
                $draft['sessions'][$sessionIndex]['exercices'][$exerciseIndex]['type'] = match ($type) {
                    'force', 'strength' => 'musculation',
                    'mobility', 'mobilite', 'moi' => 'mobilite',
                    default => $type,
                };

                if (is_int($exercise['repos'] ?? null) || is_float($exercise['repos'] ?? null)) {
                    $draft['sessions'][$sessionIndex]['exercices'][$exerciseIndex]['repos'] = $exercise['repos'].' seconds';
                }
            }
        }

        return $draft;
    }
}
