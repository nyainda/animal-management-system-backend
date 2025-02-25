<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create supplier_categories table for better organization
        Schema::create('supplier_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id')->nullable(); // Changed to UUID
            $table->foreign('category_id')->references('id')->on('supplier_categories')->onDelete('set null');
            // Basic Information
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('website')->nullable();
            $table->string('tax_number')->unique()->nullable();

            // Address Information
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Business Information
            $table->enum('type', ['feed', 'medication', 'equipment', 'service', 'other']);
            $table->string('product_type')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('business_registration_number')->unique()->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();

            // Banking Information
            $table->string('account_holder')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('iban')->nullable();

            // Operational Information
            $table->enum('supplier_importance', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('inventory_level')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->integer('minimum_order_quantity')->default(0);
            $table->integer('lead_time_days')->default(0);

            // Financial Information
            $table->enum('payment_terms', ['immediate', 'net15', 'net30', 'net60', 'net90'])->nullable();
            $table->decimal('credit_limit', 10, 2)->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0);

            // Performance Metrics
            $table->decimal('supplier_rating', 3, 2)->nullable();
            $table->integer('total_orders')->default(0);
            $table->integer('fulfilled_orders')->default(0);
            $table->integer('delayed_orders')->default(0);
            $table->integer('quality_incidents')->default(0);

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted'])->default('active');
            $table->text('notes')->nullable();

            // Meta Information
            $table->json('meta_data')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            // Indexes
            $table->index('name');
            $table->index('type');
            $table->index('product_type');
            $table->index('supplier_importance');
            $table->index('status');
            $table->index(['created_at', 'updated_at']);
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supplier_id');
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('animal_supplier', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('supplier_id');
            $table->string('relationship_type')->nullable(); // e.g., primary, secondary
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->unique(['animal_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_supplier');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('supplier_categories');
    }
};
