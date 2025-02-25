<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('healths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id'); // Remove nullable since we want to track who created it
            $table->string('health_status')->nullable();
            $table->string('vaccination_status')->nullable();
            $table->uuid('vet_contact_id')->nullable();
            $table->json('medical_history')->nullable();
            $table->json('dietary_restrictions')->nullable();
            $table->boolean('neutered_spayed')->nullable();
            $table->json('regular_medication')->nullable();
            $table->date('last_vet_visit')->nullable();
            $table->string('insurance_details')->nullable();
            $table->json('exercise_requirements')->nullable();
            $table->json('parasite_prevention')->nullable();
            $table->json('vaccinations')->nullable();
            $table->json('allergies')->nullable();
            $table->json('notes')->nullable();
            $table->timestamps();

            // Update foreign key constraint to match non-nullable requirement
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Changed to cascade since the column is required

            $table->foreign('animal_id')
                ->references('id')
                ->on('animals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('healths');
    }
};
