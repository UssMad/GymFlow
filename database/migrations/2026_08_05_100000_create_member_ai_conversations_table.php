<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('member_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('member_ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['member', 'assistant']);
            $table->text('contenu');
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_ai_messages');
        Schema::dropIfExists('member_ai_conversations');
    }
};
