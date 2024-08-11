<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGameSessionRequest;
use App\Http\Requests\UpdateGameSessionRequest;
use App\Models\GameSession;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GameSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('GameSessions/Index', [
            'game_sessions' => GameSession::with('partecipants')->get(),
            'create_url'    => route('game-sessions.index'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('GameSessions/Form', );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGameSessionRequest $request)
    {
        GameSession::create([
             ...$request->validated(),
            'user_id' => auth()->id(),
            // TODO: Make sure slug is unique
            'slug'    => Str::slug($request->title),
        ]);

        return to_route('game-sessions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(GameSession $game_session)
    {
        return Inertia::render('GameSessions/Show', [
            'game_session' => $game_session->load('partecipants'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GameSession $game_session)
    {
        return Inertia::render('GameSessions/Edit', [
            'gameSession' => $game_session,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGameSessionRequest $request, GameSession $game_session)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GameSession $game_session)
    {
        //
    }
}
