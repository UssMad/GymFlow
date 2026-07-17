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
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('members')->cascadeOnDelete();
            $table->enum('statut', ['en_attente', 'terminee', 'echec'])->default('en_attente');
            $table->json('contexte_utilise');
            $table->json('reponse_brute')->nullable();
            $table->timestamp('generee_le')->useCurrent();
            $table->timestamps();

            $table->index(['membre_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
