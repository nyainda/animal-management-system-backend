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
            $table->string('transaction_type'); // 'income' (sale) or 'expense' (ENUM recommended)
            $table->date('transaction_date');
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable(); // Detailed description of the transaction
            $table->string('category')->nullable(); // e.g., 'feed', 'medication', 'labor', 'veterinary', 'sale', etc. (ENUM)
            $table->uuid('related_id')->nullable(); // Link to other records (e.g., animal sale transaction, purchase record)
            $table->unsignedBigInteger('user_id')->nullable(); // Who recorded the transaction
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

             $table->index('transaction_type');
             $table->index('transaction_date');
             $table->index('category');
             $table->index('user_id');

             // Example:  Linking to Animal Sale (If you have a separate sales table)
Schema::table('financial_transactions', function (Blueprint $table) {
    $table->foreign('related_id')->references('id')->on('transactions')->onDelete('set null'); // Set null on delete for sales transaction
});


//Example: Linking to Inventory Purchases (If you have a separate purchases table)
Schema::table('financial_transactions', function (Blueprint $table) {
    $table->foreign('related_id')->references('id')->on('inventory_movements')->onDelete('set null'); // Set null on delete for purchases
});
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
