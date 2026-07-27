<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre_id' => $this->membre_id,
            'date_presence' => $this->date_presence->toDateString(),
            'enregistre_le' => $this->enregistre_le,
            'notes' => $this->notes,
            'member' => $this->whenLoaded('member', fn (): array => [
                'id' => $this->member->id,
                'nom' => $this->member->user->nom,
                'prenom' => $this->member->user->prenom,
            ]),
        ];
    }
}
