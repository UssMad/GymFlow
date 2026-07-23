<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiGenerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membre_id' => $this->membre_id,
            'demande_par_coach_id' => $this->demande_par_coach_id,
            'statut' => $this->statut,
            'generee_le' => $this->generee_le,
            'error_code' => $this->when($this->statut === 'echec', data_get($this->reponse_brute, 'error_code')),
            'error' => $this->when($this->statut === 'echec', data_get($this->reponse_brute, 'error')),
        ];
    }
}
