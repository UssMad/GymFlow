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
        Schema::create('exercise_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seance_id')->constrained('workout_sessions')->cascadeOnDelete();
            $table->foreignId('exercice_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordre');
            $table->unsignedSmallInteger('series')->nullable();
            $table->unsignedSmallInteger('repetitions')->nullable();
            $table->string('repos')->nullable();
            $table->decimal('charge', 6, 2)->nullable();
            $table->unsignedSmallInteger('duree_cardio')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['seance_id', 'ordre']);
            $table->index(['seance_id', 'exercice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_details');
    }
};
