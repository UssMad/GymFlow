<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberAiConversation extends Model
{
    protected $fillable = [
        'membre_id',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MemberAiMessage::class, 'conversation_id');
    }
}
