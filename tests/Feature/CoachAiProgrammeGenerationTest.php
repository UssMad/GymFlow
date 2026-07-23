<?php

use App\Ai\Agents\WorkoutProgrammeGenerator;
use App\Jobs\GenerateWorkoutProgrammeDraft;
use App\Models\AiGeneration;
use App\Models\Coach;
use App\Models\Member;
use App\Models\SportProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createAiCoach(): Coach
{
    $user = User::factory()->create(['role' => 'coach']);

    return Coach::query()->create([
        'user_id' => $user->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);
}

function createAiMember(Coach $coach, bool $withProfile = true): Member
{
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);

    if ($withProfile) {
        SportProfile::query()->create([
            'membre_id' => $member->id,
            'objectif' => 'Prise de masse',
            'niveau' => 'intermediaire',
            'blessures' => 'Aucune',
            'jours_disponibles' => ['lundi', 'mercredi', 'vendredi'],
            'preferences' => 'Poids libres',
        ]);
    }

    return $member;
}

it('queues an AI programme generation for an assigned member profile', function () {
    Queue::fake();
    $coach = createAiCoach();
    $member = createAiMember($coach);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->postJson("/api/coach/members/{$member->id}/ai-generations")
        ->assertAccepted()
        ->assertJsonPath('data.membre_id', $member->id)
        ->assertJsonPath('data.demande_par_coach_id', $coach->id)
        ->assertJsonPath('data.statut', 'en_attente');

    $this->assertDatabaseHas('ai_generations', [
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'statut' => 'en_attente',
    ]);
    Queue::assertPushed(GenerateWorkoutProgrammeDraft::class);
});

it('requires a profile and prevents another coach from requesting generation', function () {
    Queue::fake();
    $coach = createAiCoach();
    $memberWithoutProfile = createAiMember($coach, false);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->postJson("/api/coach/members/{$memberWithoutProfile->id}/ai-generations")
        ->assertUnprocessable();

    $member = createAiMember($coach);
    $otherCoach = createAiCoach();
    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->postJson("/api/coach/members/{$member->id}/ai-generations")
        ->assertForbidden();
});

it('stores structured AI output as an unpublished programme draft', function () {
    $coach = createAiCoach();
    $member = createAiMember($coach);
    $generation = AiGeneration::query()->create([
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'contexte_utilise' => [
            'objectif' => 'Prise de masse',
            'niveau' => 'intermediaire',
            'jours_disponibles' => ['lundi'],
            'preferences' => 'Poids libres',
        ],
        'generee_le' => now(),
    ]);
    WorkoutProgrammeGenerator::fake([[
        'titre' => 'Programme prise de masse',
        'sessions' => [[
            'jour' => 'Lundi',
            'notes' => 'Haut du corps',
            'exercices' => [[
                'nom' => 'Developpe couche',
                'groupe_musculaire' => 'Pectoraux',
                'type' => 'musculation',
                'series' => 4,
                'repetitions' => 10,
                'repos' => '90 secondes',
                'duree_cardio' => 0,
                'notes' => 'Controle du mouvement.',
                'progression' => 'Ajouter une repetition si possible.',
            ]],
        ]],
    ]])->preventStrayPrompts();

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(app(WorkoutProgrammeGenerator::class));

    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'statut' => 'terminee']);
    $this->assertDatabaseHas('programmes', [
        'generation_id' => $generation->id,
        'membre_id' => $member->id,
        'statut' => 'brouillon',
        'source' => 'ia',
    ]);
    $this->assertDatabaseCount('workout_sessions', 1);
    $this->assertDatabaseCount('exercise_details', 1);
    expect($generation->fresh()->reponse_brute)
        ->toHaveKey('titre')
        ->toHaveKey('sessions.0.exercices.0.progression');
    WorkoutProgrammeGenerator::assertPrompted(fn ($prompt) => str($prompt->prompt)->contains('Prise de masse'));
});

it('marks the generation as failed when the AI provider throws', function () {
    $coach = createAiCoach();
    $member = createAiMember($coach);
    $generation = AiGeneration::query()->create([
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'contexte_utilise' => ['objectif' => 'Endurance', 'niveau' => 'debutant'],
        'generee_le' => now(),
    ]);
    WorkoutProgrammeGenerator::fake([fn () => throw new RuntimeException('Provider unavailable')]);

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(app(WorkoutProgrammeGenerator::class));

    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'statut' => 'echec']);
    $this->assertDatabaseCount('programmes', 0);
});
