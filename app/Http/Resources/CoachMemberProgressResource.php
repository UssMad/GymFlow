<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachMemberProgressResource extends JsonResource
{
    public function __construct(mixed $resource, private readonly array $summary)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'member' => [
                'id' => $this->id,
                'nom' => $this->user->nom,
                'prenom' => $this->user->prenom,
            ],
            'summary' => [
                'total_sessions' => $this->summary['total_sessions'],
                'completed_sessions' => $this->summary['completed_sessions'],
                'completion_rate' => $this->summary['completion_rate'],
                'last_completed_at' => $this->summary['last_completed_at'],
                'difficulty' => $this->summary['difficulty'],
            ],
            'recent_completed_sessions' => $this->summary['recent_completed_sessions']->map(fn ($session): array => [
                'id' => $session->id,
                'programme' => [
                    'id' => $session->programme->id,
                    'titre' => $session->programme->titre,
                ],
                'jour' => $session->jour,
                'realisee_le' => $session->realisee_le,
                'difficulte_ressentie' => $session->difficulte_ressentie,
                'retour_membre' => $session->retour_membre,
            ]),
        ];
    }
}
