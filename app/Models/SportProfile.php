<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportProfile extends Model
{
    protected $fillable = [
        'membre_id',
        'objectif',
        'niveau',
        'poids',
        'taille',
        'blessures',
        'jours_disponibles',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'poids' => 'decimal:2',
            'taille' => 'decimal:2',
            'jours_disponibles' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }
}
