<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('collectors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('employee_id')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('certification_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('employee_id');
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('collectors');
    }
};
