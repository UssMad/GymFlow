<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    protected $table = 'workout_sessions';

    protected $fillable = [
        'programme_id',
        'jour',
        'ordre',
        'statut',
        'notes',
        'realisee_le',
        'retour_membre',
        'difficulte_ressentie',
        'raison_non_realisation',
    ];

    protected $attributes = [
        'statut' => 'planifie',
    ];

    protected function casts(): array
    {
        return [
            'realisee_le' => 'datetime',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    public function exerciseDetails(): HasMany
    {
        return $this->hasMany(ExerciseDetail::class, 'seance_id');
    }
}
