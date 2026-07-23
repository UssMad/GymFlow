<?php

use App\Models\Coach;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createReviewCoach(): Coach
{
    $user = User::factory()->create(['role' => 'coach']);

    return Coach::query()->create([
        'user_id' => $user->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);
}

function createReviewProgramme(Coach $coach): Programme
{
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    $exercise = Exercise::query()->create([
        'nom' => 'Developpe couche',
        'groupe_musculaire' => 'Pectoraux',
        'type' => 'musculation',
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Brouillon IA',
        'source' => 'ia',
        'statut' => 'brouillon',
    ]);
    $session = $programme->sessions()->create(['jour' => 'Lundi', 'ordre' => 1]);
    $session->exerciseDetails()->create([
        'exercice_id' => $exercise->id,
        'ordre' => 1,
        'series' => 3,
        'repetitions' => 10,
    ]);

    return $programme;
}

function reviewProgrammePayload(Exercise $exercise): array
{
    return [
        'titre' => 'Brouillon IA ajuste',
        'sessions' => [[
            'jour' => 'Mercredi',
            'notes' => 'Cardio et gainage',
            'exercices' => [[
                'exercice_id' => $exercise->id,
                'series' => 5,
                'repetitions' => 6,
                'repos' => '120 secondes',
                'duree_cardio' => 20,
                'notes' => 'Augmenter progressivement.',
            ]],
        ]],
    ];
}

it('lets the assigned coach view and edit a generated programme draft', function () {
    $coach = createReviewCoach();
    $programme = createReviewProgramme($coach);
    $replacementExercise = Exercise::query()->create([
        'nom' => 'Rameur',
        'groupe_musculaire' => 'Cardio',
        'type' => 'cardio',
    ]);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->getJson("/api/coach/programmes/{$programme->id}")
        ->assertOk()
        ->assertJsonPath('data.titre', 'Brouillon IA')
        ->assertJsonPath('data.sessions.0.exercices.0.series', 3);

    $this->putJson("/api/coach/programmes/{$programme->id}", reviewProgrammePayload($replacementExercise))
        ->assertOk()
        ->assertJsonPath('data.titre', 'Brouillon IA ajuste')
        ->assertJsonPath('data.statut', 'brouillon')
        ->assertJsonPath('data.sessions.0.jour', 'Mercredi')
        ->assertJsonPath('data.sessions.0.exercices.0.exercice.id', $replacementExercise->id)
        ->assertJsonPath('data.sessions.0.exercices.0.duree_cardio', 20);

    $this->assertDatabaseHas('exercise_details', [
        'exercice_id' => $replacementExercise->id,
        'series' => 5,
        'repetitions' => 6,
    ]);
    $this->assertDatabaseCount('workout_sessions', 1);
    $this->assertDatabaseCount('exercise_details', 1);
});

it('prevents another coach from viewing or editing a programme draft', function () {
    $coach = createReviewCoach();
    $programme = createReviewProgramme($coach);
    $otherCoach = createReviewCoach();
    $exercise = Exercise::query()->firstOrFail();

    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->getJson("/api/coach/programmes/{$programme->id}")->assertForbidden();
    $this->putJson("/api/coach/programmes/{$programme->id}", reviewProgrammePayload($exercise))->assertForbidden();
});

it('requires coach validation before a programme can be published', function () {
    $coach = createReviewCoach();
    $programme = createReviewProgramme($coach);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->postJson("/api/coach/programmes/{$programme->id}/publish")
        ->assertUnprocessable();

    $this->assertDatabaseHas('programmes', ['id' => $programme->id, 'statut' => 'brouillon']);

    $this->postJson("/api/coach/programmes/{$programme->id}/validate")
        ->assertOk()
        ->assertJsonPath('data.statut', 'valide')
        ->assertJsonPath('data.coach_validateur_id', $coach->id);

    $this->assertDatabaseHas('programmes', [
        'id' => $programme->id,
        'statut' => 'valide',
        'coach_validateur_id' => $coach->id,
    ]);

    $this->postJson("/api/coach/programmes/{$programme->id}/publish")
        ->assertOk()
        ->assertJsonPath('data.statut', 'publie');

    $this->assertDatabaseHas('programmes', ['id' => $programme->id, 'statut' => 'publie']);
});

it('does not allow a validated programme to be edited', function () {
    $coach = createReviewCoach();
    $programme = createReviewProgramme($coach);
    $programme->update(['statut' => 'valide']);
    $exercise = Exercise::query()->firstOrFail();

    Sanctum::actingAs($coach->user, ['coach']);

    $this->putJson("/api/coach/programmes/{$programme->id}", reviewProgrammePayload($exercise))
        ->assertUnprocessable();
});
