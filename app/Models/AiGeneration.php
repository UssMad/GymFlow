<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiGeneration extends Model
{
    protected $fillable = [
        'membre_id',
        'demande_par_coach_id',
        'statut',
        'contexte_utilise',
        'reponse_brute',
        'generee_le',
    ];

    protected $attributes = [
        'statut' => 'en_attente',
    ];

    protected function casts(): array
    {
        return [
            'contexte_utilise' => 'array',
            'reponse_brute' => 'array',
            'generee_le' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }

    public function requestingCoach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'demande_par_coach_id');
    }

    public function programme(): HasOne
    {
        return $this->hasOne(Programme::class, 'generation_id');
    }
}
