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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id'); // Associated animal
            $table->uuid('user_id'); // Assigned user (non-nullable)
            $table->string('title'); // Task title
            $table->string('task_type'); // Type of task (e.g., feeding, vaccination, milking)
            $table->date('start_date')->nullable(); // Start date of the task
            $table->time('start_time')->nullable(); // Start time of the task
            $table->date('end_date')->nullable(); // End date of the task
            $table->time('end_time')->nullable(); // End time of the task
            $table->integer('duration')->nullable(); // Duration of the task in minutes
            $table->text('description')->nullable(); // Task description
            $table->text('health_notes')->nullable(); // Health-related notes
            $table->string('location')->nullable(); // Location where the task is performed
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // Task priority
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending'); // Task status
            $table->enum('repeats', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none'); // Recurrence
            $table->integer('repeat_frequency')->nullable(); // Frequency of recurrence (e.g., every 2 days)
            $table->date('end_repeat_date')->nullable(); // End date for recurrence
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('animal_id')
            ->references('id')
            ->on('animals')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
