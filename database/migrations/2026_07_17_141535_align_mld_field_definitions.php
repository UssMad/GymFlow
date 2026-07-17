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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nom');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('prenom')->after('nom');
        });

        Schema::table('coaches', function (Blueprint $table) {
            $table->string('specialite')->change();
            $table->string('disponibilite')->change();
        });

        Schema::table('sport_profiles', function (Blueprint $table) {
            $table->json('jours_disponibles')->change();
            $table->text('preferences')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sport_profiles', function (Blueprint $table) {
            $table->json('preferences')->nullable()->change();
            $table->json('jours_disponibles')->nullable()->change();
        });

        Schema::table('coaches', function (Blueprint $table) {
            $table->json('disponibilite')->nullable()->change();
            $table->string('specialite')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('prenom');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nom', 'name');
        });
    }
};
