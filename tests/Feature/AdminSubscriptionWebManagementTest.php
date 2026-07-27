<?php

use App\Models\Member;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an administrator create a subscription plan and assign it to a member', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);

    $this->actingAs($admin)->post('/admin/subscription-plans', [
        'nom' => 'Monthly',
        'duree_jours' => 30,
        'description' => 'Gym access',
    ])->assertSessionHas('status');

    $planId = SubscriptionPlan::query()->value('id');

    $this->actingAs($admin)->post("/admin/members/{$member->id}/subscriptions", [
        'subscription_plan_id' => $planId,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(29)->toDateString(),
    ])->assertSessionHas('status');

    $this->assertDatabaseHas('member_subscriptions', ['member_id' => $member->id, 'subscription_plan_id' => $planId, 'statut' => 'actif']);
    $this->assertDatabaseHas('members', ['id' => $member->id, 'statut_abonnement' => 'actif']);
});
