<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('job_title')->nullable(); // e.g., 'Farm Manager', 'Herdsman', 'Field Worker'
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->nullable(); // ENUM
            $table->text('notes')->nullable(); // Any additional notes about the employee
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key to users table (if employees are also users)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users'); // If employees are also users

            // Add indexes
            $table->index('first_name');
            $table->index('last_name');
            $table->index('job_title');
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
