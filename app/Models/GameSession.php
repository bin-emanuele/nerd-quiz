<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at'];

    protected $casts = [];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    /**
     * RELATIONS
     */
    public function partecipants(): HasMany
    {
        return $this->hasMany(Partecipant::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * SCOPES
     */
    public function scopeJoinable(Builder $query): Builder
    {
        return $query->whereIn('status', ['waiting-partecipants', 'writing-question']);
    }

    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
