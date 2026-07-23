<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'coach_id',
        'date_inscription',
        'statut_abonnement',
    ];

    protected $attributes = [
        'statut_abonnement' => 'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_inscription' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function sportProfile(): HasOne
    {
        return $this->hasOne(SportProfile::class, 'membre_id');
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class, 'membre_id');
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class, 'membre_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }
}
