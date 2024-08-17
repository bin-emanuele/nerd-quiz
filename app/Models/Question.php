<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'closed_at'  => 'datetime',
    ];

    /**
     * RELATIONS
     */
    public function game_session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function booked_by(): BelongsTo
    {
        return $this->belongsTo(Partecipant::class);
    }
}
