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
        Schema::create('sport_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->string('objectif');
            $table->enum('niveau', ['debutant', 'intermediaire', 'avance']);
            $table->decimal('poids', 5, 2)->nullable();
            $table->decimal('taille', 5, 2)->nullable();
            $table->text('blessures')->nullable();
            $table->json('jours_disponibles')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sport_profiles');
    }
};
