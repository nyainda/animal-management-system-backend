<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create event categories table with integration points
        Schema::create('calendar_event_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_system')->default(false); // To identify system categories
            $table->timestamps();
        });

        // Create event types table with specialized fields
        Schema::create('calendar_event_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('requires_outcome')->default(false);
            $table->boolean('tracks_cost')->default(false);
            $table->boolean('allows_attachments')->default(true);
            $table->timestamps();

            $table->foreign('category_id')
                  ->references('id')
                  ->on('calendar_event_categories')
                  ->onDelete('cascade');
        });

        // Create reminder templates
        Schema::create('calendar_reminder_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_type_id');
            $table->string('name');
            $table->text('message_template');
            $table->json('notification_channels'); // ['email', 'sms', 'push']
            $table->json('timing_rules'); // Multiple reminder timings
            $table->timestamps();

            $table->foreign('event_type_id')
                  ->references('id')
                  ->on('calendar_event_types')
                  ->onDelete('cascade');
        });

        // Create recurrence patterns table
        Schema::create('calendar_recurrence_patterns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('frequency'); // daily, weekly, monthly, yearly
            $table->integer('interval')->default(1);
            $table->json('days_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('occurrence_count')->nullable();
            $table->json('exclusion_dates')->nullable(); // Skip specific dates
            $table->timestamps();
        });

        // Enhanced calendar events table with integration
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('event_type_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->uuid('recurrence_pattern_id')->nullable();
            $table->uuid('parent_event_id')->nullable();

            // Integration with other modules
            $table->uuid('health_record_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->uuid('breeding_id')->nullable();
            $table->uuid('feed_schedule_id')->nullable();
            $table->uuid('feed_record_id')->nullable();
            $table->uuid('yield_record_id')->nullable();
            $table->uuid('supplier_id')->nullable();

            // Event metadata and tracking
            $table->json('metadata')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->json('attachments')->nullable();

            // Location details
            $table->string('location_name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // User tracking
            $table->uuid('created_by');
            $table->uuid('updated_by');
            $table->uuid('completed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys for existing relationships
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('event_type_id')->references('id')->on('calendar_event_types')->onDelete('restrict');
            $table->foreign('recurrence_pattern_id')->references('id')->on('calendar_recurrence_patterns')->onDelete('set null');
            $table->foreign('parent_event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->foreign('health_record_id')->references('id')->on('health')->onDelete('set null');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->foreign('breeding_id')->references('id')->on('breedings')->onDelete('set null');
            $table->foreign('feed_schedule_id')->references('id')->on('feeding_schedules')->onDelete('set null');
            $table->foreign('feed_record_id')->references('id')->on('feeding_records')->onDelete('set null');
            $table->foreign('yield_record_id')->references('id')->on('yield_records')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('completed_by')->references('id')->on('users');

            // Indexes for performance
            $table->index('start_time');
            $table->index('end_time');
            $table->index('status');
            $table->index(['animal_id', 'start_time']);
            $table->index(['event_type_id', 'start_time']);
        });

        // Create event reminders table
        Schema::create('calendar_event_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('template_id')->nullable();
            $table->integer('remind_before_minutes');
            $table->json('notification_channels');
            $table->text('custom_message')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('calendar_events')
                  ->onDelete('cascade');
            $table->foreign('template_id')
                  ->references('id')
                  ->on('calendar_reminder_templates')
                  ->onDelete('set null');
        });

        // Create event participants table
        Schema::create('calendar_event_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->string('role')->default('participant');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('calendar_events')
                  ->onDelete('cascade');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_participants');
        Schema::dropIfExists('calendar_event_reminders');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendar_reminder_templates');
        Schema::dropIfExists('calendar_recurrence_patterns');
        Schema::dropIfExists('calendar_event_types');
        Schema::dropIfExists('calendar_event_categories');
    }
};
