<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CompleteWorkoutSessionRequest;
use App\Http\Resources\WorkoutSessionResource;
use App\Models\WorkoutSession;

class MemberWorkoutSessionController extends Controller
{
    public function complete(CompleteWorkoutSessionRequest $request, WorkoutSession $workoutSession): WorkoutSessionResource
    {
        $member = $request->user()->member;
        $programme = $workoutSession->programme;

        abort_unless($member && $programme->membre_id === $member->id && $programme->statut === 'publie', 404);

        $data = $request->validated();
        $workoutSession->update([
            'statut' => 'realise',
            'realisee_le' => $workoutSession->realisee_le ?? now(),
            'retour_membre' => $data['retour_membre'] ?? $workoutSession->retour_membre,
            'difficulte_ressentie' => $data['difficulte_ressentie'] ?? $workoutSession->difficulte_ressentie,
            'raison_non_realisation' => null,
        ]);

        return new WorkoutSessionResource($workoutSession->fresh()->load('exerciseDetails.exercise'));
    }
}
