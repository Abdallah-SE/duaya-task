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
        Schema::create('idle_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Simple settings for idle monitoring (as per task requirements)
            $table->unsignedTinyInteger('idle_timeout')->default(5); // Timeout in seconds (default 5s as per task)
            $table->boolean('idle_monitoring_enabled')->default(true);
            $table->unsignedTinyInteger('max_idle_warnings')->default(2); // Max warnings before logout (default 2)
            
            $table->timestamps();
            
            // Ensure one settings record per user
            $table->unique('user_id');
            
            // Performance indexes
            $table->index('idle_monitoring_enabled');
            $table->index('idle_timeout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idle_settings');
    }
};