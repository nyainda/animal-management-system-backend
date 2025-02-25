<?php

// database/migrations/2024_12_29_create_animal_activities_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('animal_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id');
            $table->string('activity_type');
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('activity_date');
            $table->text('notes')->nullable();
            $table->date('breeding_date')->nullable();
            $table->text('breeding_notes')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->string('auto_type')->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['animal_id', 'activity_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('animal_activities');
    }
};
