<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->smallInteger('max_partecipants')->default(25);

            // TODO: Manage status with a state machine
            $table->enum('status', [
                'waiting-partecipants',
                'writing-question',
                'waiting-booking',
                'answer-booked',
                'answer-verification',
                'game-over',
            ])->default('waiting-partecipants');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
