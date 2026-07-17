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
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->cascadeOnDelete();
            $table->string('jour');
            $table->unsignedSmallInteger('ordre');
            $table->enum('statut', ['planifie', 'realise', 'non_realise', 'reporte'])->default('planifie');
            $table->text('notes')->nullable();
            $table->timestamp('realisee_le')->nullable();
            $table->text('retour_membre')->nullable();
            $table->text('raison_non_realisation')->nullable();
            $table->timestamps();

            $table->unique(['programme_id', 'ordre']);
            $table->index(['programme_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
