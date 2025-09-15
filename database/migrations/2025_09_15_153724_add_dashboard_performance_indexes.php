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
        // Add indexes for activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            // Composite index for user_id and created_at for efficient user activity queries
            $table->index(['user_id', 'created_at'], 'idx_activity_logs_user_created');
            
            // Index for action column for filtering by action type
            $table->index('action', 'idx_activity_logs_action');
            
            // Index for created_at for date-based queries
            $table->index('created_at', 'idx_activity_logs_created_at');
            
            // Composite index for action and created_at for CRUD operations breakdown
            $table->index(['action', 'created_at'], 'idx_activity_logs_action_created');
        });

        // Add indexes for idle_sessions table
        Schema::table('idle_sessions', function (Blueprint $table) {
            // Composite index for user_id and created_at
            $table->index(['user_id', 'created_at'], 'idx_idle_sessions_user_created');
            
            // Index for idle_ended_at for filtering completed sessions
            $table->index('idle_ended_at', 'idx_idle_sessions_ended_at');
            
            // Composite index for user_id and idle_ended_at for user session stats
            $table->index(['user_id', 'idle_ended_at'], 'idx_idle_sessions_user_ended');
        });

        // Add indexes for penalties table
        Schema::table('penalties', function (Blueprint $table) {
            // Composite index for user_id and date
            $table->index(['user_id', 'date'], 'idx_penalties_user_date');
            
            // Index for date column for date-based queries
            $table->index('date', 'idx_penalties_date');
            
            // Index for reason column for filtering by reason type
            $table->index('reason', 'idx_penalties_reason');
        });

        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            // Index for updated_at for active users queries
            $table->index('updated_at', 'idx_users_updated_at');
        });

        // Add indexes for employees table
        Schema::table('employees', function (Blueprint $table) {
            // Index for user_id for efficient joins
            $table->index('user_id', 'idx_employees_user_id');
            
            // Index for department for filtering by department
            $table->index('department', 'idx_employees_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for activity_logs table
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_user_created');
            $table->dropIndex('idx_activity_logs_action');
            $table->dropIndex('idx_activity_logs_created_at');
            $table->dropIndex('idx_activity_logs_action_created');
        });

        // Drop indexes for idle_sessions table
        Schema::table('idle_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_idle_sessions_user_created');
            $table->dropIndex('idx_idle_sessions_ended_at');
            $table->dropIndex('idx_idle_sessions_user_ended');
        });

        // Drop indexes for penalties table
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropIndex('idx_penalties_user_date');
            $table->dropIndex('idx_penalties_date');
            $table->dropIndex('idx_penalties_reason');
        });

        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_updated_at');
        });

        // Drop indexes for employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_user_id');
            $table->dropIndex('idx_employees_department');
        });
    }
};