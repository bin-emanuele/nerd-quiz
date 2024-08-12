<?php

use App\Models\GameSession;
use App\Models\Partecipant;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('game-session.{slug}', function (User | Partecipant $member, string $slug) {
    $game_session = GameSession::bySlug($slug)->firstOrFail();

    if ($member instanceof User) {
        Log::info('User ' . $member->id . ' is authorized to join channel game-session.' . $slug);
        $member['type'] = 'host';
        return $member;
    }

    if (!$game_session->partecipants->contains($member->id)) {
        return false;
    }

    Log::info('Partecipant ' . $member->id . ' is authorized to join channel game-session.' . $slug);

    $member['type'] = 'partecipant';
    return $member;
}, [
    'guards' => ['web', 'partecipant'],
]);