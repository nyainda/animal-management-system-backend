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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('user_id')->nullable();// Who should receive the notification
            $table->string('notification_type'); // e.g., 'vaccination_due', 'low_feed_inventory', 'animal_birth', 'report_ready'
            $table->string('title'); // Short title for the notification
            $table->text('message'); // Detailed message
            $table->boolean('is_read')->default(false); // Has the user read the notification?
            $table->timestamp('sent_at')->useCurrent(); // When the notification was sent
            $table->timestamp('read_at')->nullable(); // When the user read the notification
            $table->uuid('related_id')->nullable(); // ID of the related record (animal, etc.)
            $table->string('related_type')->nullable(); // Type of the related record (e.g., 'animal')

            $table->foreign('user_id')->references('id')
            ->on('users')->onDelete('cascade');

            $table->foreign('animal_id')
            ->references('id')
            ->on('animals')
            ->onDelete('cascade');

            $table->index(['user_id', 'is_read']); // Index for efficient queries
            $table->index('notification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
