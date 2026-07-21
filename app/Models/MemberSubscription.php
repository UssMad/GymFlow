<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberSubscription extends Model
{
    protected $fillable = [
        'member_id',
        'subscription_plan_id',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function resolvedStatus(): string
    {
        if ($this->statut === 'suspendu') {
            return 'suspendu';
        }

        return $this->date_fin->lt(today()) ? 'expire' : 'actif';
    }
}
