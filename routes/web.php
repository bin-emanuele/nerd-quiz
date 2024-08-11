<?php

use App\Http\Controllers\FrontGameSessionController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\HandleFrontInertiaRequests;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [FrontGameSessionController::class, 'index'])->name('home')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware(HandleFrontInertiaRequests::class);
Route::get('/game/{slug}', [FrontGameSessionController::class, 'show'])->name('game-sessions.front.show')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware(HandleFrontInertiaRequests::class);
Route::post('/game/{slug}/join', [FrontGameSessionController::class, 'join'])->name('game-sessions.front.join')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware(HandleFrontInertiaRequests::class);
Route::post('/game/{slug}/leave', [FrontGameSessionController::class, 'leave'])->name('game-sessions.front.leave')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware(HandleFrontInertiaRequests::class);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('game-sessions', GameSessionController::class);
});

require __DIR__ . '/auth.php';
