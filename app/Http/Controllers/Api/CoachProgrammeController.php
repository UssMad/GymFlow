<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreManualProgrammeRequest;
use App\Http\Requests\Api\UpdateProgrammeRequest;
use App\Http\Resources\ProgrammeResource;
use App\Models\Member;
use App\Models\Programme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoachProgrammeController extends Controller
{
    public function show(Request $request, Programme $programme): ProgrammeResource
    {
        $this->ensureCoachOwnsProgramme($request, $programme);

        return new ProgrammeResource($programme->load('sessions.exerciseDetails.exercise'));
    }

    public function store(StoreManualProgrammeRequest $request, Member $member): JsonResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $member->coach_id === $coach->id, 403);

        $programme = DB::transaction(function () use ($request, $member): Programme {
            $data = $request->validated();

            $programme = Programme::query()->create([
                'membre_id' => $member->id,
                'titre' => $data['titre'],
                'source' => 'manuel',
                'statut' => 'brouillon',
                'date_debut' => $data['date_debut'] ?? null,
                'date_fin' => $data['date_fin'] ?? null,
            ]);

            foreach ($data['sessions'] as $sessionIndex => $sessionData) {
                $session = $programme->sessions()->create([
                    'jour' => $sessionData['jour'],
                    'ordre' => $sessionIndex + 1,
                    'notes' => $sessionData['notes'] ?? null,
                ]);

                foreach ($sessionData['exercices'] as $exerciseIndex => $exerciseData) {
                    $session->exerciseDetails()->create([
                        'exercice_id' => $exerciseData['exercice_id'],
                        'ordre' => $exerciseIndex + 1,
                        'series' => $exerciseData['series'] ?? null,
                        'repetitions' => $exerciseData['repetitions'] ?? null,
                        'repos' => $exerciseData['repos'] ?? null,
                        'charge' => $exerciseData['charge'] ?? null,
                        'duree_cardio' => $exerciseData['duree_cardio'] ?? null,
                        'notes' => $exerciseData['notes'] ?? null,
                    ]);
                }
            }

            return $programme;
        });

        return (new ProgrammeResource($programme->load('sessions.exerciseDetails.exercise')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProgrammeRequest $request, Programme $programme): ProgrammeResource
    {
        $this->ensureCoachOwnsProgramme($request, $programme);
        abort_unless($programme->statut === 'brouillon', 422, 'Only draft programmes can be edited.');

        DB::transaction(function () use ($request, $programme): void {
            $data = $request->validated();
            $programme->update([
                'titre' => $data['titre'],
                'date_debut' => $data['date_debut'] ?? null,
                'date_fin' => $data['date_fin'] ?? null,
            ]);

            $programme->sessions()->delete();

            foreach ($data['sessions'] as $sessionIndex => $sessionData) {
                $session = $programme->sessions()->create([
                    'jour' => $sessionData['jour'],
                    'ordre' => $sessionIndex + 1,
                    'notes' => $sessionData['notes'] ?? null,
                ]);

                foreach ($sessionData['exercices'] as $exerciseIndex => $exerciseData) {
                    $session->exerciseDetails()->create([
                        'exercice_id' => $exerciseData['exercice_id'],
                        'ordre' => $exerciseIndex + 1,
                        'series' => $exerciseData['series'] ?? null,
                        'repetitions' => $exerciseData['repetitions'] ?? null,
                        'repos' => $exerciseData['repos'] ?? null,
                        'charge' => $exerciseData['charge'] ?? null,
                        'duree_cardio' => $exerciseData['duree_cardio'] ?? null,
                        'notes' => $exerciseData['notes'] ?? null,
                    ]);
                }
            }
        });

        return new ProgrammeResource($programme->fresh()->load('sessions.exerciseDetails.exercise'));
    }

    private function ensureCoachOwnsProgramme(Request $request, Programme $programme): void
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $programme->member->coach_id === $coach->id, 403);
    }
}
