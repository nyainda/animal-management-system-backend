<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Feed Types Table - Now includes user_id and animal_id
        Schema::create('feed_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., 'grain', 'hay', 'supplement'
            $table->string('recommended_storage')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->text('nutritional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('animal_id')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
        });

        // 2. Feed Inventory Table - Added user_id and animal_id
        Schema::create('feed_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->uuid('feed_type_id');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->decimal('unit_price', 10, 2);
            $table->string('currency')->default('USD');
            $table->date('purchase_date');
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('animal_id')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
            $table->foreign('feed_type_id')
                  ->references('id')
                  ->on('feed_types')
                  ->onDelete('restrict');
        });

        // 3. Feeding Schedules Table - Updated user_id constraint
        Schema::create('feeding_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->uuid('feed_type_id');
            $table->time('feeding_time');
            $table->decimal('portion_size', 8, 2);
            $table->string('portion_unit');
            $table->string('frequency');
            $table->json('days_of_week')->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('animal_id')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
            $table->foreign('feed_type_id')
                  ->references('id')
                  ->on('feed_types')
                  ->onDelete('restrict');
        });

        // 4. Feeding Records Table - Updated user relationship
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->uuid('feed_type_id');
            $table->uuid('feed_inventory_id');
            $table->uuid('schedule_id')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('unit');
            $table->decimal('cost', 10, 2);
            $table->string('currency')->default('USD');
            $table->datetime('fed_at');
            $table->text('notes')->nullable();
            $table->string('feeding_method')->nullable();
            $table->text('consumption_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('animal_id')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
            $table->foreign('feed_type_id')
                  ->references('id')
                  ->on('feed_types')
                  ->onDelete('restrict');
            $table->foreign('feed_inventory_id')
                  ->references('id')
                  ->on('feed_inventory')
                  ->onDelete('restrict');
            $table->foreign('schedule_id')
                  ->references('id')
                  ->on('feeding_schedules')
                  ->onDelete('set null');
        });

        // 5. Feed Analytics Table - Added user_id
        Schema::create('feed_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('animal_id');
            $table->uuid('feed_type_id');
            $table->date('analysis_date');
            $table->decimal('daily_consumption', 10, 2);
            $table->string('consumption_unit');
            $table->decimal('daily_cost', 10, 2);
            $table->string('currency')->default('USD');
            $table->decimal('waste_percentage', 5, 2)->nullable();
            $table->json('consumption_patterns')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreign('animal_id')
                  ->references('id')
                  ->on('animals')
                  ->onDelete('cascade');
            $table->foreign('feed_type_id')
                  ->references('id')
                  ->on('feed_types')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_analytics');
        Schema::dropIfExists('feeding_records');
        Schema::dropIfExists('feeding_schedules');
        Schema::dropIfExists('feed_inventory');
        Schema::dropIfExists('feed_types');
    }
};
