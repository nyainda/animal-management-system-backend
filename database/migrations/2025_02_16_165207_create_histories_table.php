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
        Schema::create('histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id')->nullable(); // Who performed the action (can be null for system actions)
            $table->string('action_type'); // e.g., 'animal_created', 'animal_updated', 'feeding_record_added', 'medication_administered', 'report_generated', etc.
            $table->uuid('related_id')->nullable(); // ID of the related record (animal, feeding record, etc.)
            $table->string('related_type')->nullable(); // Type of the related record (e.g., 'animal', 'feeding_record')
            $table->text('description')->nullable(); // Detailed description of the action
            $table->timestamp('recorded_at')->useCurrent(); // Timestamp of the action
            $table->json('old_data')->nullable(); // Previous data (if applicable) in JSON format
            $table->json('new_data')->nullable(); // New data (if applicable) in JSON format

            $table->foreign('user_id')->references('id')
            ->on('users')->onDelete('cascade');

            $table->foreign('animal_id')
            ->references('id')
            ->on('animals')
            ->onDelete('cascade');

            $table->index(['action_type', 'recorded_at']); // Index for efficient queries
            $table->index(['related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
