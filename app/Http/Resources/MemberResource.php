<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_inscription' => $this->date_inscription?->toDateString(),
            'statut_abonnement' => $this->statut_abonnement,
            'user' => new UserResource($this->whenLoaded('user')),
            'coach' => $this->whenLoaded('coach', fn (): ?array => $this->coach ? [
                'id' => $this->coach->id,
                'nom' => $this->coach->user?->nom,
                'prenom' => $this->coach->user?->prenom,
                'specialite' => $this->coach->specialite,
            ] : null),
            'sport_profile' => $this->whenLoaded('sportProfile', fn (): ?array => $this->sportProfile ? [
                'id' => $this->sportProfile->id,
                'objectif' => $this->sportProfile->objectif,
                'niveau' => $this->sportProfile->niveau,
                'blessures' => $this->sportProfile->blessures,
                'jours_disponibles' => $this->sportProfile->jours_disponibles,
                'preferences' => $this->sportProfile->preferences,
            ] : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
