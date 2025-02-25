<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('yield_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id');
            $table->uuid('product_category_id');
            $table->uuid('product_grade_id');
            $table->uuid('production_method_id');
            $table->uuid('collector_id')->nullable();
            $table->uuid('storage_location_id')->nullable();

            // Production Details
            $table->decimal('quantity', 10, 2);
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->date('production_date');
            $table->time('production_time');

            // Quality Control
            $table->string('quality_status'); // Passed, Failed, Under Review
            $table->text('quality_notes')->nullable();
            $table->string('trace_number')->nullable();

            // Weather and Conditions
            $table->json('weather_conditions')->nullable(); // temp, humidity, etc.
            $table->json('storage_conditions')->nullable();

            // Additional Info
            $table->boolean('is_organic')->default(false);
            $table->string('certification_number')->nullable();
            $table->json('additional_attributes')->nullable();
            $table->text('notes')->nullable();

            // Timestamps and Soft Delete
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('animal_id')
            ->references('id')
            ->on('animals');

            $table->foreign('product_category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('restrict');

            $table->foreign('product_grade_id')
                  ->references('id')
                  ->on('product_grades')
                  ->onDelete('restrict');

            $table->foreign('production_method_id')
                  ->references('id')
                  ->on('production_methods')
                  ->onDelete('restrict');

            $table->foreign('collector_id')
                  ->references('id')
                  ->on('collectors')
                  ->onDelete('set null');

            $table->foreign('storage_location_id')
                  ->references('id')
                  ->on('storage_locations')
                  ->onDelete('set null');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Indexes
            $table->index('production_date');
            $table->index('trace_number');
            $table->index(['animal_id', 'production_date','user_id']);
            $table->index(['product_category_id', 'production_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('yield_records');
    }
};
