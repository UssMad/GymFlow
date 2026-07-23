<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SportProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre_id' => $this->membre_id,
            'objectif' => $this->objectif,
            'niveau' => $this->niveau,
            'poids' => $this->poids,
            'taille' => $this->taille,
            'blessures' => $this->blessures,
            'jours_disponibles' => $this->jours_disponibles,
            'preferences' => $this->preferences,
            'updated_at' => $this->updated_at,
        ];
    }
}
