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
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('generation_id')->nullable()->unique()->constrained('ai_generations')->nullOnDelete();
            $table->foreignId('coach_validateur_id')->nullable()->constrained('coaches')->nullOnDelete();
            $table->string('titre');
            $table->enum('statut', ['brouillon', 'valide', 'publie'])->default('brouillon');
            $table->enum('source', ['ia', 'manuel']);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();

            $table->index(['membre_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
