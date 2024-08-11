<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partecipant extends Authenticatable
{
    use HasFactory;

    protected $guarded = ['id', 'created_at'];

    /**
     * RELATIONS
     */
    public function game_session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
