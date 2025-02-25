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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('transaction_type', ['income', 'expense']); // Using enum instead of string
            $table->date('transaction_date');
            $table->uuid('animal_id');
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable(); // Detailed description of the transaction
            $table->enum('category', ['feed', 'medication', 'labor', 'veterinary', 'sale'])->nullable(); // Using enum instead of string
            $table->uuid('related_id')->nullable(); // Link to other records (e.g., animal sale transaction, purchase record)
            $table->uuid('user_id')->nullable(); // Who recorded the transaction
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('restrict');

            // Indexes
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('category');
            $table->index('user_id');
        });

        // Add polymorphic relationship for related_id
        // This allows relating to different tables based on a type column
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('related_type')->nullable()->after('related_id');
            $table->index(['related_id', 'related_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
