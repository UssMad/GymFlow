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
        if (Schema::hasTable('member_subscriptions')) {
            $foreignKeys = Schema::getForeignKeys('member_subscriptions');
            $hasPlanForeignKey = collect($foreignKeys)->contains(
                fn (array $foreignKey): bool => $foreignKey['name'] === 'member_subscriptions_subscription_plan_id_foreign',
            );
            $indexes = Schema::getIndexes('member_subscriptions');
            $hasHistoryIndex = collect($indexes)->contains(
                fn (array $index): bool => $index['name'] === 'member_subscriptions_member_date_fin_index',
            );

            Schema::table('member_subscriptions', function (Blueprint $table) use ($hasPlanForeignKey, $hasHistoryIndex) {
                if (! $hasPlanForeignKey) {
                    $table->foreign('subscription_plan_id')
                        ->references('id')
                        ->on('subscription_plans')
                        ->restrictOnDelete();
                }

                if (! $hasHistoryIndex) {
                    $table->index(['member_id', 'date_fin'], 'member_subscriptions_member_date_fin_index');
                }
            });

            return;
        }

        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['actif', 'expire', 'suspendu'])->default('actif');
            $table->timestamps();

            $table->index(['member_id', 'date_fin'], 'member_subscriptions_member_date_fin_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
    }
};
