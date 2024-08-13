<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at'];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_correct'  => 'boolean',
    ];

    /**
     * RELATIONS
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function partecipant(): BelongsTo
    {
        return $this->belongsTo(Partecipant::class);
    }

    public function game_session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
