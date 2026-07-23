<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['membre_id', 'date_presence', 'enregistre_le', 'notes'];

    protected function casts(): array
    {
        return ['date_presence' => 'date', 'enregistre_le' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'membre_id');
    }
}
