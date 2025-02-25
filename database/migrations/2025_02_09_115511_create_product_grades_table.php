<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_category_id');
            $table->string('grade_name'); // e.g., A, B, Premium
            $table->text('description')->nullable();
            $table->decimal('price_modifier', 8, 2)->default(1.00); // Price multiplier
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('cascade');

            $table->index(['product_category_id', 'grade_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_grades');
    }
};
