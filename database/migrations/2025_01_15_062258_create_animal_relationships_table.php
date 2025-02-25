<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('animal_relationships')) {
        Schema::create('animal_relationships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('animal_id');
            $table->uuid('related_animal_id');
            $table->string('relationship_type'); // 'dam', 'sire', 'offspring'
            $table->date('breeding_date')->nullable();
            $table->text('breeding_notes')->nullable();
            $table->timestamps();

            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
            $table->foreign('related_animal_id')->references('id')->on('animals')->onDelete('cascade');

            // Prevent duplicate relationships with a shorter constraint name
            $table->unique(
                ['animal_id', 'related_animal_id', 'relationship_type'],
                'animal_rel_unique'
            );
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('animal_relationships');
    }
};
