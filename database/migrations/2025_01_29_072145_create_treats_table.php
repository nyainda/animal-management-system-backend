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
        Schema::create('treats', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Foreign Keys with Indexes
            $table->uuid('animal_id');
            $table->uuid('user_id')->nullable();
             // Category managed at application level

            // Status and Verification
            $table->string('status')->default('scheduled');
            $table->boolean('is_verified')->default(false);
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            // Treatment Core Details
            $table->string('type'); // Treatment Type (e.g., Vaccination, Medication)
            $table->string('product')->nullable(); // Product Name or Details
            $table->string('batch_number')->nullable(); // Batch tracking
            $table->string('manufacturer')->nullable();
            $table->date('expiry_date')->nullable();

            // Dosage and Application
            $table->decimal('dosage', 10, 3)->nullable();
            $table->decimal('inventory_used', 10, 3)->nullable();
            $table->string('unit')->nullable(); // Unit of Measurement (e.g., ml, kg)
            $table->string('administration_route')->nullable(); // Moved to enums
            $table->string('administration_site')->nullable();

            // Withdrawal and Follow-up
            $table->unsignedInteger('withdrawal_days')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->date('next_treatment_date')->nullable();
            $table->boolean('requires_followup')->default(false);

            // Personnel and Cost
            $table->string('technician_name')->nullable();
            $table->uuid('technician_id')->nullable(); // Reference managed at application level
            $table->string('currency', 3)->default('USD');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->boolean('record_transaction')->default(false);

            // Documentation
            $table->text('notes')->nullable();
            $table->date('treatment_date');
            $table->time('treatment_time')->nullable();
            $table->json('tags')->nullable();
            $table->string('attachment_path')->nullable();

            // Reason and Results
            $table->text('reason')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('outcome')->nullable();
            $table->json('vital_signs')->nullable();

            // Timestamps and Soft Deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('animal_id');
            $table->index('user_id');
            $table->index('treatment_date');
            $table->index('status');
            $table->index(['animal_id', 'treatment_date']);

            // Foreign Key Constraints - Only essential ones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treats');
    }
};
