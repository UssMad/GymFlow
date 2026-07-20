<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    protected $fillable = [
        'membre_id',
        'generation_id',
        'coach_validateur_id',
        'titre',
        'statut',
        'source',
        'date_debut',
        'date_fin',
        'date_validation',
    ];

    protected $attributes = [
        'statut' => 'brouillon',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_validation' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class, 'generation_id');
    }

    public function validatingCoach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'coach_validateur_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class, 'programme_id');
    }
}
