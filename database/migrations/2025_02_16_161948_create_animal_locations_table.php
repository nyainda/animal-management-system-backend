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
        Schema::create('animal_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->decimal('latitude', 10, 8)->nullable(); // Allow nullable for cases where location isn't available
            $table->decimal('longitude', 10, 8)->nullable(); // Allow nullable
            $table->timestamp('recorded_at')->useCurrent(); // Timestamp of location recording
            $table->string('location_description')->nullable(); // Optional: e.g., 'Pasture A'
            $table->json('geofence_details')->nullable(); // Store geofence information (if using)
            $table->timestamps();

            $table->foreign('user_id')->references('id')
            ->on('users')->onDelete('cascade');

            $table->foreign('animal_id')
            ->references('id')
            ->on('animals')
            ->onDelete('cascade');

            $table->index(['animal_id', 'recorded_at']); // Index for efficient queries
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->unsignedBigInteger('current_location_id')->nullable(); // Add this line

            $table->foreign('current_location_id')->references('id')->on('animal_locations')->onDelete('set null'); // Add this line
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_locations');
    }
};
