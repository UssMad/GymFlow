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
        'image_url',
        'niveau',
    ];

    public static function imageForType(?string $type): string
    {
        return match ($type) {
            'cardio' => 'https://images.unsplash.com/photo-1538805060514-97d9cc17730c?auto=format&fit=crop&w=400&q=80',
            'mobilite' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=400&q=80',
            default => 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=400&q=80',
        };
    }

    public function resolvedImageUrl(): string
    {
        return $this->image_url ?: self::imageForType($this->type);
    }

    public function exerciseDetails(): HasMany
    {
        return $this->hasMany(ExerciseDetail::class, 'exercice_id');
    }
}
