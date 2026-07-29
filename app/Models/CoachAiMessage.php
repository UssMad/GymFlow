<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachAiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'contenu',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CoachAiConversation::class, 'conversation_id');
    }
}
