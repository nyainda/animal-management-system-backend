<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('content')->nullable();
            $table->string('category')->nullable();
            $table->json('keywords')->nullable();
            $table->string('file_path')->nullable();
            $table->uuid('animal_id');
            $table->uuid('user_id')->nullable();
            $table->boolean('add_to_calendar')->default(false);
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->enum('status', ['pending', 'completed', 'archived'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
