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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('members')->cascadeOnDelete();
            $table->date('date_presence');
            $table->timestamp('enregistre_le')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['membre_id', 'date_presence']);
            $table->index('date_presence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
