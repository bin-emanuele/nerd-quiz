<?php

use App\Http\Controllers\FrontGameSessionController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\HandleFrontInertiaRequests;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;

Route::middleware([HandleFrontInertiaRequests::class])
    ->withoutMiddleware([HandleInertiaRequests::class])
    ->group(function () {
        Route::get('/', [FrontGameSessionController::class, 'index'])->name('home');
        Route::get('/game/{slug}', [FrontGameSessionController::class, 'show'])->name('game-sessions.front.show');
        Route::get('/game/{slug}/data', [FrontGameSessionController::class, 'data'])->name('game-sessions.front.data');
        Route::post('/game/{slug}/join', [FrontGameSessionController::class, 'join'])->name('game-sessions.front.join');
        Route::post('/game/{slug}/leave', [FrontGameSessionController::class, 'leave'])->name('game-sessions.front.leave');
        Route::post('/game/{slug}/book/{question}', [FrontGameSessionController::class, 'book'])->name('game-sessions.front.book');
        Route::post('/game/{slug}/answer/{question}', [FrontGameSessionController::class, 'answer'])->name('game-sessions.front.answer');
    });

Route::post('game-sessions/{game_session}/reset', [GameSessionController::class, 'reset'])->name('game-sessions.reset');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('game-sessions/{game_session}/writing', [GameSessionController::class, 'writingQuestion'])->name('game-sessions.writing-question');
    Route::post('game-sessions/{game_session}/question', [GameSessionController::class, 'nextQuestion'])->name('game-sessions.next-question');
    Route::post('game-sessions/{game_session}/answer/{answer}/confirm', [GameSessionController::class, 'confirmAnswer'])->name('game-sessions.confirm-answer');
    Route::resource('game-sessions', GameSessionController::class);

    // Duplicate the route used to replace the 'dashboard' route
    Route::get('dashboard', [GameSessionController::class, 'index'])->name('dashboard');

});

require __DIR__ . '/auth.php';
