<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');

            // User IDs for registered users (optional)
            $table->uuid('seller_id')->nullable(); // If seller is a registered user
            $table->uuid('buyer_id')->nullable();  // If buyer is a registered user

            // Farm Reference removed as requested

            // Transaction Details
            $table->enum('transaction_type', [
                'sale',
                'purchase',
                'lease',
                'transfer',
                'donation',
                'exchange',
                'breeding_fee'
            ]);
            $table->decimal('price', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->date('transaction_date');
            $table->datetime('delivery_date')->nullable();
            $table->text('details')->nullable();

            // Payment Information
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'credit_card',
                'check',
                'crypto',
                'payment_plan'
            ])->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->date('payment_due_date')->nullable();

            // Transaction Status
            $table->enum('transaction_status', [
                'pending',
                'deposit_paid',
                'in_progress',
                'completed',
                'cancelled',
                'refunded',
                'disputed'
            ])->default('pending');

            // Seller Information (for non-registered sellers)
            $table->string('seller_name')->nullable();
            $table->string('seller_company')->nullable();
            $table->string('seller_tax_id')->nullable();
            $table->string('seller_contact')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_address')->nullable();
            $table->string('seller_city')->nullable();
            $table->string('seller_state')->nullable();
            $table->string('seller_country')->nullable();
            $table->string('seller_postal_code')->nullable();
            $table->string('seller_identification')->nullable();
            $table->string('seller_license_number')->nullable();

            // Buyer Information (for non-registered buyers)
            $table->string('buyer_name')->nullable();
            $table->string('buyer_company')->nullable();
            $table->string('buyer_tax_id')->nullable();
            $table->string('buyer_contact')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('buyer_city')->nullable();
            $table->string('buyer_state')->nullable();
            $table->string('buyer_country')->nullable();
            $table->string('buyer_postal_code')->nullable();
            $table->string('buyer_identification')->nullable();
            $table->string('buyer_license_number')->nullable();

            // Documentation
            $table->string('invoice_number')->unique()->nullable();
            $table->string('contract_number')->unique()->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->datetime('terms_accepted_at')->nullable();
            $table->string('health_certificate_number')->nullable();
            $table->string('transport_license_number')->nullable();
            $table->json('attached_documents')->nullable();

            // Additional Information
            $table->string('location_of_sale')->nullable();
            $table->json('terms_and_conditions')->nullable();
            $table->text('special_conditions')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->decimal('insurance_amount', 10, 2)->nullable();

            // Tracking
            $table->uuid('created_by')->nullable(); // User who recorded the transaction
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users');
            $table->foreign('buyer_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            // Indexes
            $table->index('animal_id');
            $table->index('seller_id');
            $table->index('buyer_id');
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('transaction_status');
            $table->index('invoice_number');
            $table->index('contract_number');
            $table->index(['seller_name', 'seller_identification']);
            $table->index(['buyer_name', 'buyer_identification']);
        });

        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id');
            $table->uuid('recorded_by')->nullable(); // User who recorded the payment
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'credit_card',
                'check',
                'crypto'
            ]);
            $table->string('payment_reference')->nullable();
            $table->datetime('payment_date');
            $table->string('payment_status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users');
            $table->index('transaction_id');
            $table->index('payment_date');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('transactions');
    }
};
