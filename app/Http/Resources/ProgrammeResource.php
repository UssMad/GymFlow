<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgrammeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre_id' => $this->membre_id,
            'generation_id' => $this->generation_id,
            'coach_validateur_id' => $this->coach_validateur_id,
            'titre' => $this->titre,
            'statut' => $this->statut,
            'source' => $this->source,
            'date_debut' => $this->date_debut?->toDateString(),
            'date_fin' => $this->date_fin?->toDateString(),
            'date_validation' => $this->date_validation,
            'sessions' => WorkoutSessionResource::collection($this->whenLoaded('sessions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
