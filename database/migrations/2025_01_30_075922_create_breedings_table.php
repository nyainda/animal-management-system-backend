<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breedings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');  // The primary animal (usually female)
            $table->uuid('mate_id');    // The breeding partner (usually male)
            $table->uuid('user_id');

            // Breeding process tracking
            $table->enum('breeding_status', [
                'planned',
                'in_progress',
                'successful',
                'unsuccessful',
                'cancelled'
            ])->default('planned');

            // Important dates
            $table->date('heat_date')->nullable();
            $table->date('breeding_date')->nullable();
            $table->date('due_date')->nullable();

            // Pregnancy tracking
            $table->enum('pregnancy_status', [
                'not_pregnant',
                'suspected',
                'confirmed',
                'delivered',
                'miscarried'
            ])->default('not_pregnant');

            // Offspring tracking
            $table->integer('offspring_count')->nullable();
            $table->json('offspring_details')->nullable();

            // Notes and additional info
            $table->text('remarks')->nullable();
            $table->json('health_notes')->nullable();

            // Foreign keys and relationships
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('mate_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['animal_id', 'mate_id']);
            $table->index(['breeding_date', 'due_date']);
            $table->index('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breedings');
    }
};
