<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('animals')) {
            Schema::create('animals', function (Blueprint $table) {
                // Primary and Foreign Keys
                $table->uuid('id')->primary();
                $table->uuid('user_id');

                // Core Attributes
                $table->string('name', 100)->index();
                $table->string('type', 50)->index();
                $table->string('breed', 50)->nullable()->index();
                $table->string('status', 20)->index()->default('active');
                $table->string('internal_id', 50)->unique();
                $table->string('tag_number', 30)->nullable()->index();

                // Biological Attributes
                $table->enum('gender', ['male', 'female'])->nullable();
                $table->date('birth_date')->nullable()->index();
                $table->date('death_date')->nullable()->index();
                $table->string('deceased_reason', 255)->nullable();

                // Physical Attributes
                $table->decimal('weight', 8, 2)->nullable()->comment('In kilograms');
                $table->decimal('height', 8, 2)->nullable()->comment('In centimeters');
                $table->decimal('horn_length', 8, 2)->nullable();
                $table->decimal('retention_score', 3, 2)->nullable();
                $table->decimal('body_condition_score', 3, 1)->nullable();
                $table->string('coloring', 30)->nullable();
                $table->boolean('is_neutered')->default(false);
                $table->boolean('is_breeding_stock')->default(false);

                // JSON Storage
                $table->json('keywords')->nullable();
                $table->json('physical_traits')->nullable();
                $table->json('identification_details')->nullable();

                // Relationships
                //$table->uuid('dam_id')->nullable()->index();
               // $table->uuid('dam_id')->nullable()->index();
                //$table->uuid('sire_id')->nullable()->index();

                // Timestamps
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index(['type', 'status', 'birth_date']);
                $table->index(['user_id', 'type', 'status']);
                $table->index('created_at');

                // Foreign Keys
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                //$table->foreign('dam_id')->references('id')->on('animals')->nullOnDelete();
               // $table->foreign('sire_id')->references('id')->on('animals')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('animal_birth_details')) {
            Schema::create('animal_birth_details', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('animal_id')->unique();

                // Birth Metrics
                $table->decimal('birth_weight', 8, 2)->nullable();
                $table->string('weight_unit', 10)->nullable();
                $table->time('birth_time')->nullable();
                $table->string('birth_status', 30);
                $table->unsignedTinyInteger('colostrum_intake')->nullable();

                // Medical Information
                $table->string('health_at_birth', 50);
                $table->json('vaccinations')->nullable();
                $table->text('milk_feeding')->nullable();

                // Breeding Information
                $table->boolean('multiple_birth')->default(false);
                $table->unsignedTinyInteger('birth_order')->nullable();
                $table->unsignedSmallInteger('gestation_length')->nullable()->comment('In days');
                $table->string('breeder_info', 100)->nullable();
                $table->enum('raised_purchased', ['raised', 'purchased'])->default('raised');

                // Multimedia
                $table->json('birth_photos')->nullable();

                // Timestamps
                $table->timestamps();

                // Foreign Key
                $table->foreign('animal_id')
                    ->references('id')
                    ->on('animals')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_birth_details');
        Schema::dropIfExists('animals');
    }
};
