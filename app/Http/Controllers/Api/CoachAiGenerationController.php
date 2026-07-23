<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AiGenerationResource;
use App\Jobs\GenerateWorkoutProgrammeDraft;
use App\Models\AiGeneration;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachAiGenerationController extends Controller
{
    public function store(Request $request, Member $member): JsonResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $member->coach_id === $coach->id, 403);

        $profile = $member->sportProfile;
        abort_unless($profile, 422, 'A sports profile is required before AI generation.');

        $generation = AiGeneration::query()->create([
            'membre_id' => $member->id,
            'demande_par_coach_id' => $coach->id,
            'statut' => 'en_attente',
            'contexte_utilise' => [
                'objectif' => $profile->objectif,
                'niveau' => $profile->niveau,
                'poids' => $profile->poids,
                'taille' => $profile->taille,
                'blessures' => $profile->blessures,
                'jours_disponibles' => $profile->jours_disponibles,
                'preferences' => $profile->preferences,
                'historique_programmes' => $member->programmes()->latest('id')->limit(3)->get(['titre', 'statut'])->all(),
            ],
            'generee_le' => now(),
        ]);

        GenerateWorkoutProgrammeDraft::dispatch($generation->id);

        return (new AiGenerationResource($generation))
            ->additional(['message' => 'Programme generation queued for coach review.'])
            ->response()
            ->setStatusCode(202);
    }
}
