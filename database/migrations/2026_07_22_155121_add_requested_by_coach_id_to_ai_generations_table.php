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
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->foreignId('demande_par_coach_id')
                ->nullable()
                ->constrained('coaches')
                ->nullOnDelete()
                ->after('membre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_par_coach_id');
        });
    }
};
