<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('production_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_category_id');
            $table->string('method_name');
            $table->text('description')->nullable();
            $table->boolean('requires_certification')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('cascade');

            $table->index(['product_category_id', 'method_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_methods');
    }
};
