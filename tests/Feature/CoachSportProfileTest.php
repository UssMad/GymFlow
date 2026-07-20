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

it('allows a coach to create or update an assigned member sports profile', function () {
    $coach = createCoach();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);

    Sanctum::actingAs($coach->user, ['coach']);

    $payload = [
        'objectif' => 'Perte de poids',
        'niveau' => 'debutant',
        'poids' => 85.50,
        'taille' => 180.00,
        'blessures' => 'Genou droit sensible',
        'jours_disponibles' => ['mardi', 'jeudi'],
        'preferences' => 'Cardio a faible impact',
    ];

    $this->putJson("/api/coach/members/{$member->id}/sport-profile", $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Sport profile saved successfully.')
        ->assertJsonPath('data.objectif', 'Perte de poids')
        ->assertJsonPath('data.jours_disponibles.1', 'jeudi');

    $this->putJson("/api/coach/members/{$member->id}/sport-profile", [
        ...$payload,
        'objectif' => 'Endurance',
    ])
        ->assertOk()
        ->assertJsonPath('data.objectif', 'Endurance');

    $this->assertDatabaseHas('sport_profiles', [
        'membre_id' => $member->id,
        'objectif' => 'Endurance',
        'niveau' => 'debutant',
    ]);
});

it('validates sports-profile data and protects unassigned members from updates', function () {
    $coach = createCoach();
    $member = createMemberForCoach($coach);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->putJson("/api/coach/members/{$member->id}/sport-profile", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['objectif', 'niveau', 'jours_disponibles', 'preferences']);

    $otherCoach = createCoach();
    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->putJson("/api/coach/members/{$member->id}/sport-profile", [
        'objectif' => 'Endurance',
        'niveau' => 'avance',
        'jours_disponibles' => ['samedi'],
        'preferences' => 'Course',
    ])->assertForbidden();
});
