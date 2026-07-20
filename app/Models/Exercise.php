<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = [
        'nom',
        'groupe_musculaire',
        'type',
        'description',
        'equipement',
        'niveau',
    ];

    public function exerciseDetails(): HasMany
    {
        return $this->hasMany(ExerciseDetail::class, 'exercice_id');
    }
}
