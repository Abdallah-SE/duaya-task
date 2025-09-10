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
        Schema::create('idle_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Session tracking (as per task requirement: "Log number of idle sessions per user")
            $table->timestamp('idle_started_at'); // When idle state started
            $table->timestamp('idle_ended_at')->nullable(); // When idle state ended
            $table->unsignedInteger('duration_seconds')->nullable(); // Calculated duration (idle_ended_at - idle_started_at)
            
            $table->timestamps();
            
            // Performance indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['idle_started_at', 'idle_ended_at']);
            $table->index('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idle_sessions');
    }
};
