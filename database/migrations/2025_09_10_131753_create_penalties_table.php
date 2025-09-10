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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reason', 255)->nullable(); 
            $table->unsignedInteger('count')->default(1);
            $table->date('date'); // Date when penalty was applied (as per task requirements)
            
            $table->timestamps();
            
            // Performance indexes
            $table->index(['user_id', 'date']);
            $table->index('date');
            $table->index('count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
