<?php

namespace App\Http\Controllers;

use App\Events\GameSession\AnswerResult;
use App\Events\GameSession\GameOver;
use App\Events\GameSession\NextQuestion;
use App\Events\GameSession\ResetGame;
use App\Events\GameSession\WritingQuestion;
use App\Http\Requests\GameSession\NextQuestionGameSessionRequest;
use App\Http\Requests\GameSession\StoreGameSessionRequest;
use App\Http\Requests\GameSession\UpdateGameSessionRequest;
use App\Jobs\GameSession\QuestionTimeout as QuestionTimeoutJob;
use App\Models\Answer;
use App\Models\GameSession;
use Illuminate\Http\Request;
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
            'game_session'          => $game_session->load('partecipants', 'questions', 'questions.answers', 'questions.answers.partecipant', 'questions.booked_by'),
            'winning_answers_count' => config('app.game.winning_answers_count'),
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

    public function reset(GameSession $game_session)
    {
        $game_session->update([
            'status' => 'waiting-partecipants',
        ]);

        $game_session->questions()->each(function ($question) {
            $question->answers()->delete();
            $question->delete();
        });

        $game_session->partecipants()->each(function ($partecipant) {
            $partecipant->answers_available = config('app.game.answers_available');
            $partecipant->answers_correct   = 0;
            $partecipant->save();
        });

        ResetGame::dispatch($game_session);

        return response()->json(['message' => 'Game session resetted!', 'game_session' => $game_session->refresh()->load('partecipants', 'questions', 'questions.answers', 'questions.answers.partecipant', 'questions.booked_by')]);
    }

    public function writingQuestion(GameSession $game_session)
    {
        if ($game_session->status !== 'waiting-partecipants') {
            return response()->json(['message' => 'This game session is not accepting new questions!'], 422);
        }

        $game_session->update([
            'status' => 'writing-question',
        ]);

        WritingQuestion::dispatch($game_session);

        return response()->json(['message' => 'You can now write questions!', 'status' => 'writing-question']);
    }

    public function nextQuestion(GameSession $game_session, NextQuestionGameSessionRequest $request)
    {
        if ($game_session->status !== 'writing-question') {
            return response()->json(['message' => 'This game session is not accepting new questions!'], 422);
        }

        $question = $game_session->questions()->create([
            'text'       => $request->input('text'),
            'expires_at' => now()->addSeconds(config('app.game.question_expires_in')),
        ]);

        $game_session->update([
            'status' => 'waiting-booking',
        ]);

        NextQuestion::dispatch($game_session, $question);
        QuestionTimeoutJob::dispatch($game_session, $question)->delay($question->expires_at);

        return response()->json(['message' => 'Question created!', 'question' => $question->refresh()]);
    }

    public function confirmAnswer(GameSession $game_session, Answer $answer, Request $request)
    {
        if ($game_session->status !== 'answer-check') {
            return response()->json(['message' => 'This game session is not in the state to check the answer!'], 422);
        }

        $request->validate([
            'is_correct' => 'required|boolean',
        ]);

        $answer->update([
            'is_correct' => $request->input('is_correct'),
            'expires_at' => now(),
        ]);

        if ($request->input('is_correct')) {
            $answer->partecipant->increment('answers_correct');
            $status = $answer->partecipant->answers_correct >= config('app.game.winning_answers_count') ? 'game-over' : 'writing-question';

            $game_session->status = $status;

            $answer->question->update([
                'booked_by_id' => null,
                'closed_at'    => now(),
            ]);

            AnswerResult::dispatch($answer);

            if ($status === 'game-over') {
                $game_session->ended_at = now();
                GameOver::dispatch($game_session);
            } else {
                WritingQuestion::dispatch($game_session);
            }
            $game_session->save();
        } else {
            $answer->partecipant->decrement('answers_available');

            $game_session->update([
                'status' => 'waiting-booking',
            ]);

            $answer->question->update([
                'booked_by_id' => null,
                'expires_at'   => now()->addSeconds(config('app.game.retry_question_expires_in')),
            ]);

            AnswerResult::dispatch($answer);

            NextQuestion::dispatch($game_session, $answer->question);
            QuestionTimeoutJob::dispatch($game_session, $answer->question)->delay($answer->question->expires_at);
        }

        return response()->json(['message' => 'Question confirmed!', 'answer' => $answer->refresh()]);
    }
}
