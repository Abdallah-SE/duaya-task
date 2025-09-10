<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the activity_logs table to track all user activities
     * including CRUD operations, login/logout events, and important actions.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Core activity information (as per task requirements)
            $table->string('action', 50); // e.g., create, read, update, delete, approve, login, logout
            
            // Polymorphic relationship to the record that was affected
            $table->nullableMorphs('subject'); // subject_type and subject_id
            
            // Device/browser info (as per task requirements)
            $table->ipAddress('ip_address')->nullable();
            $table->string('device', 100)->nullable();
            $table->string('browser', 100)->nullable();
            
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
