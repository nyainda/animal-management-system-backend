<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('location_code')->unique();
            $table->text('description')->nullable();
            $table->json('storage_conditions')->nullable(); // temperature, humidity, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('location_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('storage_locations');
    }
};
