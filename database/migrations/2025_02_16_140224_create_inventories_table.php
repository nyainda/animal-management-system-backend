<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., 'Feed (Dairy)', 'Vaccine X', 'Medication Y'
            $table->string('category'); // e.g., 'Feed', 'Medication', 'Supplies', 'Equipment' (Consider ENUM)
            $table->string('unit'); // e.g., 'kg', 'liters', 'bottles', 'units' (Consider ENUM)
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_point')->default(0); // Level at which to reorder
            $table->decimal('cost_per_unit', 10, 2)->nullable(); // Cost of each unit
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('category');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->uuid('inventory_item_id');
            $table->integer('quantity_change'); // Positive for additions, negative for subtractions
            $table->string('movement_type'); // e.g., 'purchase', 'usage', 'adjustment', 'sale' (Consider ENUM)
            $table->text('reason')->nullable(); // Why the change occurred
            $table->unsignedBigInteger('user_id')->nullable(); // Who made the change
            $table->timestamps();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index('inventory_item_id');
            $table->index('movement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
    }
};
