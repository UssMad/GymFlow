<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseDetail extends Model
{
    protected $fillable = [
        'seance_id',
        'exercice_id',
        'ordre',
        'series',
        'repetitions',
        'repos',
        'charge',
        'duree_cardio',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'charge' => 'decimal:2',
        ];
    }

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'seance_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercice_id');
    }
}
