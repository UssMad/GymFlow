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
use Throwable;

class GenerateWorkoutProgrammeDraft implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $generationId) {}

    public function handle(WorkoutProgrammeGenerator $agent, WorkoutProgrammeDraftValidator $validator): void
    {
        $generation = AiGeneration::query()->find($this->generationId);

        if (! $generation || $generation->statut !== 'en_attente') {
            return;
        }

        try {
            $draft = $agent->prompt(WorkoutProgrammePrompt::for(WorkoutProgrammePrompt::context($generation->contexte_utilise)))->toArray();
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
}
