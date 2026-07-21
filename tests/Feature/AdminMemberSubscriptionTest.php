<?php

use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createSubscriptionMember(): Member
{
    return Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
}

function createSubscriptionPlan(): SubscriptionPlan
{
    return SubscriptionPlan::query()->create([
        'nom' => 'Mensuel',
        'duree_jours' => 30,
        'description' => 'Acces mensuel a la salle',
    ]);
}

it('requires an authenticated admin to assign subscriptions', function () {
    $member = createSubscriptionMember();

    $this->postJson("/api/admin/members/{$member->id}/subscriptions", [])
        ->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create(['role' => 'coach']), ['coach']);

    $this->getJson("/api/admin/members/{$member->id}/subscriptions")
        ->assertForbidden();
});

it('assigns a subscription and synchronizes the current member status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = createSubscriptionMember();
    $plan = createSubscriptionPlan();

    Sanctum::actingAs($admin, ['admin']);

    $this->postJson("/api/admin/members/{$member->id}/subscriptions", [
        'subscription_plan_id' => $plan->id,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->addDays(29)->toDateString(),
    ])
        ->assertCreated()
        ->assertJsonPath('message', 'Subscription assigned successfully.')
        ->assertJsonPath('data.statut', 'actif')
        ->assertJsonPath('data.subscription_plan.nom', 'Mensuel');

    $this->assertDatabaseHas('member_subscriptions', [
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'statut' => 'actif',
    ]);
    $this->assertDatabaseHas('members', ['id' => $member->id, 'statut_abonnement' => 'actif']);
});

it('lists member subscription history with calculated expired status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = createSubscriptionMember();
    $plan = createSubscriptionPlan();
    MemberSubscription::query()->create([
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'date_debut' => today()->subDays(40),
        'date_fin' => today()->subDay(),
        'statut' => 'actif',
    ]);

    Sanctum::actingAs($admin, ['admin']);

    $this->getJson("/api/admin/members/{$member->id}/subscriptions")
        ->assertOk()
        ->assertJsonPath('data.0.statut', 'expire')
        ->assertJsonPath('data.0.subscription_plan.id', $plan->id);
});

it('rejects invalid subscription dates', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = createSubscriptionMember();
    $plan = createSubscriptionPlan();

    Sanctum::actingAs($admin, ['admin']);

    $this->postJson("/api/admin/members/{$member->id}/subscriptions", [
        'subscription_plan_id' => $plan->id,
        'date_debut' => today()->toDateString(),
        'date_fin' => today()->subDay()->toDateString(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['date_fin']);
});
