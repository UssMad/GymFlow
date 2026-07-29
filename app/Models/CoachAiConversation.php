<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachAiConversation extends Model
{
    protected $fillable = [
        'coach_id',
        'membre_id',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CoachAiMessage::class, 'conversation_id');
    }
}
