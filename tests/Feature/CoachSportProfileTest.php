<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\SportProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createCoach(): Coach
{
    $user = User::factory()->create(['role' => 'coach']);

    return Coach::query()->create([
        'user_id' => $user->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);
}

function createMemberForCoach(Coach $coach): Member
{
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);

    SportProfile::query()->create([
        'membre_id' => $member->id,
        'objectif' => 'Prise de masse',
        'niveau' => 'intermediaire',
        'poids' => 72.50,
        'taille' => 178.00,
        'blessures' => 'Aucune',
        'jours_disponibles' => ['lundi', 'mercredi', 'vendredi'],
        'preferences' => 'Poids libres',
    ]);

    return $member;
}

it('requires a coach token to access a sports profile', function () {
    $coach = createCoach();
    $member = createMemberForCoach($coach);

    $this->getJson("/api/coach/members/{$member->id}/sport-profile")
        ->assertUnauthorized();
});

it('allows a coach to view an assigned member sports profile', function () {
    $coach = createCoach();
    $member = createMemberForCoach($coach);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->getJson("/api/coach/members/{$member->id}/sport-profile")
        ->assertOk()
        ->assertJsonPath('data.membre_id', $member->id)
        ->assertJsonPath('data.objectif', 'Prise de masse')
        ->assertJsonPath('data.jours_disponibles.0', 'lundi');
});

it('does not allow a coach to view an unassigned member sports profile', function () {
    $assignedCoach = createCoach();
    $member = createMemberForCoach($assignedCoach);
    $otherCoach = createCoach();

    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->getJson("/api/coach/members/{$member->id}/sport-profile")
        ->assertForbidden();
});
