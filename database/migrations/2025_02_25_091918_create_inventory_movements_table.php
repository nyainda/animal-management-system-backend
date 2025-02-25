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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->enum('movement_type', ['purchase', 'consumption', 'adjustment', 'waste']);
            $table->date('movement_date');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->string('notes')->nullable();
            $table->uuid('user_id')->nullable(); // Using UUID to match users table
            $table->timestamps();

            // Foreign key constraints with correct data types
               $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
               $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');

            // Indexes for faster queries
            $table->index('movement_type');
            $table->index('movement_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
