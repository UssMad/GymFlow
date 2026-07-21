<?php

use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a subscription plan in a member subscription history', function () {
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
    $plan = SubscriptionPlan::query()->create([
        'nom' => 'Mensuel',
        'duree_jours' => 30,
        'description' => 'Acces mensuel a la salle',
    ]);

    $subscription = MemberSubscription::query()->create([
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'date_debut' => today(),
        'date_fin' => today()->addDays(29),
    ]);

    expect($member->subscriptions()->first()->id)->toBe($subscription->id)
        ->and($subscription->subscriptionPlan->nom)->toBe('Mensuel')
        ->and($subscription->resolvedStatus())->toBe('actif');
});

it('resolves a past subscription as expired while retaining its history', function () {
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
    $plan = SubscriptionPlan::query()->create([
        'nom' => 'Hebdomadaire',
        'duree_jours' => 7,
    ]);
    $subscription = MemberSubscription::query()->create([
        'member_id' => $member->id,
        'subscription_plan_id' => $plan->id,
        'date_debut' => today()->subDays(14),
        'date_fin' => today()->subDay(),
        'statut' => 'actif',
    ]);

    expect($subscription->resolvedStatus())->toBe('expire')
        ->and($member->subscriptions()->count())->toBe(1);
});
