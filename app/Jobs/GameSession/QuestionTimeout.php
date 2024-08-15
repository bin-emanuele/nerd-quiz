<?php

namespace App\Jobs\GameSession;

use App\Events\GameSession\QuestionTimeout as QuestionTimeoutEvent;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * On question timeout, the game session will be set to 'writing-question' status if still on 'waiting-booking'
 * and send a broadcast to notify all connected clients.
 * @package App\Jobs\GameSession
 */
class QuestionTimeout implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private GameSession $game_session,
        private Question $question
    ) {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->game_session->status != 'waiting-booking') {
            return;
        }

        $this->game_session->update([
            'status' => 'writing-question',
        ]);

        broadcast(new QuestionTimeoutEvent($this->game_session, $this->question));
    }
}
