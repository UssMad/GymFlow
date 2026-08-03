<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('coaches')->cascadeOnDelete();
            $table->foreignId('membre_id')->constrained('members')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coach_id', 'membre_id']);
        });

        Schema::create('coach_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('coach_ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['coach', 'assistant']);
            $table->text('contenu');
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_ai_messages');
        Schema::dropIfExists('coach_ai_conversations');
    }
};
