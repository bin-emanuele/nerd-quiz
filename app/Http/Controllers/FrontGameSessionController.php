<?php

namespace App\Http\Controllers;

use App\Events\GameSession\BookedQuestion;
use App\Events\GameSession\CheckingAnswer;
use App\Events\GameSession\PartecipantJoined;
use App\Models\GameSession;
use App\Models\Question;
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
        $game_session = GameSession::with('partecipants', 'questions', 'questions.answers', 'questions.answers.partecipant', 'questions.booked_by')->where('slug', $slug)->firstOrFail();

        return Inertia::render('GameSessions/Front', [
            'game_session'          => $game_session,
            'winning_answers_count' => config('app.game.winning_answers_count'),
            'partecipant'           => Auth::guard('partecipant')->user(),
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

        /** @var \App\Models\Partecipant */
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

    function book(string $slug, Question $question)
    {
        $game_session = GameSession::with('partecipants')->where('slug', $slug)->firstOrFail();

        if (Auth::guard('partecipant')->user()->game_session_slug !== $slug) {
            return response()->json(['message' => 'You cannot book a question on this game!'], 422);
        }

        if ($question->expires_at->isPast()) {
            return response()->json(['message' => 'This question has expired!'], 422);
        }

        if ($question->booked_by_id !== null) {
            return response()->json(['message' => "This question has already been booked by {$question->booked_by->name}!"], 422);
        }

        if ($game_session->status !== 'waiting-booking') {
            return response()->json(['message' => 'You cannot book a question on this game!'], 422);
        }

        $question->update([
            'booked_by_id' => Auth::guard('partecipant')->id(),
        ]);

        $game_session->update([
            'status' => 'answer-booked',
        ]);

        BookedQuestion::dispatch($game_session, $question->refresh()->load('booked_by'));

        return response()->json(['message' => 'Question booked!', 'question' => $question->refresh()]);
    }

    function answer(string $slug, Question $question, Request $request)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $game_session = GameSession::with('questions')->where('slug', $slug)->firstOrFail();
        $partecipant = Auth::guard('partecipant')->user();

        if ($game_session->status !== 'answer-booked') {
            return response()->json(['message' => ['This game session is not accepting answers!']]);
        }

        if ($question->game_session_id !== $game_session->id) {
            return response()->json(['message' => ['This question does not belong to this game session!']]);
        }

        if ($question->answers->contains('partecipant_id', $partecipant->id)) {
            return response()->json(['message' => ['You have already answered this question!']]);
        }

        if ($partecipant->answers_available <= 0) {
            return response()->json(['message' => ['You have no more answers available!']]);
        }

        $answer = $question->answers()->create([
            'partecipant_id' => $partecipant->id,
            'text'           => $request->input('answer'),
            'answered_at'    => now(),
        ]);
        $game_session->update([
            'status' => 'answer-check',
        ]);

        CheckingAnswer::dispatch($answer);

        return response()->json(['message' => 'Question booked!', 'question' => $question->refresh()]);
    }
}
