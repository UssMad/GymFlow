<?php

use App\Models\Coach;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createManualCoach(): Coach
{
    $user = User::factory()->create(['role' => 'coach']);

    return Coach::query()->create([
        'user_id' => $user->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);
}

function createManualMember(Coach $coach): Member
{
    return Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
}

function manualProgrammePayload(Exercise $exercise): array
{
    return [
        'titre' => 'Programme force semaine 1',
        'date_debut' => '2026-07-27',
        'date_fin' => '2026-08-02',
        'sessions' => [
            [
                'jour' => 'Lundi',
                'notes' => 'Haut du corps',
                'exercices' => [
                    [
                        'exercice_id' => $exercise->id,
                        'series' => 4,
                        'repetitions' => 8,
                        'repos' => '90 secondes',
                        'charge' => 60,
                        'notes' => 'Garder le dos droit.',
                    ],
                ],
            ],
        ],
    ];
}

it('lets a coach create a manual weekly programme draft for an assigned member', function () {
    $coach = createManualCoach();
    $member = createManualMember($coach);
    $exercise = Exercise::query()->create([
        'nom' => 'Squat barre',
        'groupe_musculaire' => 'Jambes',
        'type' => 'musculation',
    ]);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->postJson("/api/coach/members/{$member->id}/programmes", manualProgrammePayload($exercise))
        ->assertCreated()
        ->assertJsonPath('data.membre_id', $member->id)
        ->assertJsonPath('data.source', 'manuel')
        ->assertJsonPath('data.statut', 'brouillon')
        ->assertJsonPath('data.sessions.0.jour', 'Lundi')
        ->assertJsonPath('data.sessions.0.exercices.0.exercice.id', $exercise->id);

    $this->assertDatabaseHas('programmes', [
        'membre_id' => $member->id,
        'titre' => 'Programme force semaine 1',
        'source' => 'manuel',
        'statut' => 'brouillon',
    ]);
    $this->assertDatabaseHas('workout_sessions', ['jour' => 'Lundi', 'ordre' => 1]);
    $this->assertDatabaseHas('exercise_details', [
        'exercice_id' => $exercise->id,
        'series' => 4,
        'repetitions' => 8,
        'repos' => '90 secondes',
    ]);
});

it('prevents a coach from creating a programme for another coach member', function () {
    $memberCoach = createManualCoach();
    $member = createManualMember($memberCoach);
    $otherCoach = createManualCoach();
    $exercise = Exercise::query()->create([
        'nom' => 'Velo',
        'groupe_musculaire' => 'Cardio',
        'type' => 'cardio',
    ]);

    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->postJson("/api/coach/members/{$member->id}/programmes", manualProgrammePayload($exercise))
        ->assertForbidden();

    $this->assertDatabaseCount('programmes', 0);
});

it('requires at least one session and valid catalogue exercises', function () {
    $coach = createManualCoach();
    $member = createManualMember($coach);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->postJson("/api/coach/members/{$member->id}/programmes", [
        'titre' => 'Programme incomplet',
        'sessions' => [[
            'jour' => 'Lundi',
            'exercices' => [['exercice_id' => 999]],
        ]],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['sessions.0.exercices.0.exercice_id']);
});
