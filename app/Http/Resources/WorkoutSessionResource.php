<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jour' => $this->jour,
            'ordre' => $this->ordre,
            'statut' => $this->statut,
            'notes' => $this->notes,
            'realisee_le' => $this->realisee_le,
            'retour_membre' => $this->retour_membre,
            'difficulte_ressentie' => $this->difficulte_ressentie,
            'raison_non_realisation' => $this->raison_non_realisation,
            'exercices' => $this->whenLoaded('exerciseDetails', fn () => $this->exerciseDetails->map(fn ($detail): array => [
                'id' => $detail->id,
                'ordre' => $detail->ordre,
                'series' => $detail->series,
                'repetitions' => $detail->repetitions,
                'repos' => $detail->repos,
                'charge' => $detail->charge,
                'duree_cardio' => $detail->duree_cardio,
                'notes' => $detail->notes,
                'exercice' => $detail->relationLoaded('exercise') ? [
                    'id' => $detail->exercise->id,
                    'nom' => $detail->exercise->nom,
                    'groupe_musculaire' => $detail->exercise->groupe_musculaire,
                    'type' => $detail->exercise->type,
                    'equipement' => $detail->exercise->equipement,
                    'niveau' => $detail->exercise->niveau,
                ] : null,
            ])),
        ];
    }
}
