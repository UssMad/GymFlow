<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'date_debut' => $this->date_debut->toDateString(),
            'date_fin' => $this->date_fin->toDateString(),
            'statut' => $this->resolvedStatus(),
            'subscription_plan' => $this->whenLoaded('subscriptionPlan', fn (): array => [
                'id' => $this->subscriptionPlan->id,
                'nom' => $this->subscriptionPlan->nom,
                'duree_jours' => $this->subscriptionPlan->duree_jours,
                'description' => $this->subscriptionPlan->description,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
