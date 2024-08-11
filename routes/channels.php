<?php

use App\Models\GameSession;
use App\Models\Partecipant;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('game-session.{slug}', function (Partecipant $partecipant, string $slug) {
    $game_session = GameSession::bySlug($slug)->firstOrFail();

    if (!$game_session->partecipants->contains($partecipant->id)) {
        return false;
    }

    Log::info('User ' . $partecipant->id . ' is authorized to join channel game-session.' . $slug);

    return ['id' => $partecipant->id, 'name' => $partecipant->name];
}, ['guards' => ['partecipant']]);
