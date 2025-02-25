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
        Schema::create('animal_attribute_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id');
            $table->date('change_date');
            $table->string('attribute_name'); // e.g., 'weight', 'height', 'horn_length', 'body_condition_score', 'temperature', 'heart_rate', 'respiratory_rate', 'pregnancy_status', 'lameness', 'eye_condition', 'hoof_condition', 'coat_condition', 'behavior', 'location_description', 'other'
            $table->decimal('old_value', 10, 2)->nullable(); // Increased precision for some measurements
            $table->decimal('new_value', 10, 2)->nullable(); // Increased precision
            $table->string('unit')->nullable(); // Add unit of measurement (e.g., 'kg', 'cm', '°C', 'beats/min')
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->json('additional_data')->nullable(); // Store other relevant data in JSON format

            $table->timestamps();

            $table->foreign('user_id')->references('id')
            ->on('users')->onDelete('cascade');

            $table->foreign('animal_id')
            ->references('id')
            ->on('animals')
            ->onDelete('cascade');

            $table->index(['animal_id', 'change_date']);
            $table->index('attribute_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_attribute_changes');
    }
};
