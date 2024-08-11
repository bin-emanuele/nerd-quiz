<?php

namespace App\Http\Controllers;

use App\Events\GameSession\PartecipantJoined;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class FrontGameSessionController extends Controller
{
    function index()
    {
        $game_sessions = GameSession::joinable()->get();

        if (Auth::guard('partecipant')->check()) {
            return redirect()->route('game-sessions.front.show', Auth::guard('partecipant')->user()->game_session_slug);
        }

        return Inertia::render('Welcome', [
            'canLogin'      => Route::has('login'),
            'canRegister'   => Route::has('register'),
            'game_sessions' => $game_sessions,
        ]);
    }

    function show(string $slug)
    {
        $game_session = GameSession::with('partecipants')->where('slug', $slug)->firstOrFail();

        return Inertia::render('GameSessions/Front', [
            'game_session' => $game_session,
        ]);
    }

    function join(string $slug, Request $request)
    {
        $game_session = GameSession::with('partecipants')->where('slug', $slug)->firstOrFail();
        $request->validate([
            'name' => 'required|string',
        ]);

        if ($game_session->partecipants?->count() >= $game_session->max_partecipants) {
            return redirect()->back()->withErrors(['This game session is full!'], 'name');
        }

        if ($game_session->partecipants?->contains($request->input('name'))) {
            return redirect()->back()->withErrors(['This name has already been taken!'], 'name');
        }

        if ($game_session->status !== 'waiting-partecipants') {
            return redirect()->back()->withErrors(['This game session is not accepting new partecipants!'], 'name');
        }

        $partecipant = $game_session->partecipants()->create([
            'name'              => $request->input('name'),
            'answers_available' => config('app.game.answers_available'),
            'game_session_slug' => $game_session->slug,
        ]);

        PartecipantJoined::dispatch($game_session, $partecipant);

        auth('partecipant')->login($partecipant);
    }

    function leave()
    {
        auth('partecipant')->logout();

        return redirect()->route('home');
    }
}
