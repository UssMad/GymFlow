<?php

use App\Ai\Agents\WorkoutProgrammeGenerator;
use App\Ai\Validators\WorkoutProgrammeDraftValidator;
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
    WorkoutProgrammeGenerator::fake(['Here is the requested draft:\n```json\n'.json_encode([
        'titre' => 'Programme prise de masse',
        'sessions' => [[
            'jour' => 'Lundi',
            'notes' => 'Haut du corps',
            'exercices' => array_fill(0, 3, [
                'nom' => 'Developpe couche',
                'groupe_musculaire' => 'Pectoraux',
                'type' => 'isométrique',
                'series' => 4,
                'repetitions' => 10,
                'repos' => 90,
                'duree_cardio' => 0,
                'notes poste' => 'Controle du mouvement.',
                'progression' => 'Ajouter une repetition si possible.',
            ]),
        ]],
    ], JSON_THROW_ON_ERROR).'\n```'])->preventStrayPrompts();

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(
        app(WorkoutProgrammeGenerator::class),
        app(WorkoutProgrammeDraftValidator::class),
    );

    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'statut' => 'terminee']);
    $this->assertDatabaseHas('programmes', [
        'generation_id' => $generation->id,
        'membre_id' => $member->id,
        'statut' => 'brouillon',
        'source' => 'ia',
    ]);
    $this->assertDatabaseCount('workout_sessions', 1);
    $this->assertDatabaseCount('exercise_details', 3);
    $this->assertDatabaseHas('exercise_details', ['notes' => 'Controle du mouvement. Ajouter une repetition si possible.']);
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

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(
        app(WorkoutProgrammeGenerator::class),
        app(WorkoutProgrammeDraftValidator::class),
    );

    $this->assertDatabaseHas('ai_generations', [
        'id' => $generation->id,
        'statut' => 'echec',
        'reponse_brute->error_code' => 'generation_failed',
        'reponse_brute->error' => 'AI generation failed. Please try again or review the member profile.',
    ]);
    $this->assertDatabaseCount('programmes', 0);
});

it('repairs common free-model JSON variants before saving the programme draft', function () {
    $coach = createAiCoach();
    $member = createAiMember($coach);
    $generation = AiGeneration::query()->create([
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'contexte_utilise' => ['objectif' => 'Endurance', 'niveau' => 'debutant'],
        'generee_le' => now(),
    ]);
    WorkoutProgrammeGenerator::fake([<<<'JSON'
        {'titre':'Programme mobilite','sessions':[{'jour':'Lundi','notes':'Mobilite progressive','exercices':[{'nom':'Etirement des jambes','groupe_musculaire':'Mobilite','type':'mobilite','series':1,'repetitions':0,'repos':0,'duree_cardio': inconnu,'notes':'Rythme confortable.','progression':'Ajouter 5 minutes.'}]}]}
        JSON])->preventStrayPrompts();

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(
        app(WorkoutProgrammeGenerator::class),
        app(WorkoutProgrammeDraftValidator::class),
    );

    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'statut' => 'terminee']);
    $this->assertDatabaseHas('exercise_details', ['duree_cardio' => null]);
});

it('saves a safe fallback after retrying a recorded invalid AI response', function () {
    $coach = createAiCoach();
    $member = createAiMember($coach);
    $generation = AiGeneration::query()->create([
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'statut' => 'en_attente',
        'contexte_utilise' => [
            'objectif' => 'Perte de poids',
            'niveau' => 'debutant',
            'blessures' => 'Douleur a l epaule',
            'jours_disponibles' => ['mardi', 'jeudi', 'samedi'],
        ],
        'reponse_brute' => [
            'error_code' => 'invalid_response',
            'raw_response' => '{invalid JSON}',
        ],
        'generee_le' => now(),
    ]);
    WorkoutProgrammeGenerator::fake([])->preventStrayPrompts();

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(
        app(WorkoutProgrammeGenerator::class),
        app(WorkoutProgrammeDraftValidator::class),
    );

    $this->assertDatabaseHas('ai_generations', ['id' => $generation->id, 'statut' => 'terminee']);
    $this->assertDatabaseCount('workout_sessions', 3);
    $this->assertDatabaseCount('exercise_details', 9);
});

it('rejects incomplete AI output and exposes a clear error only to the assigned coach', function () {
    $coach = createAiCoach();
    $member = createAiMember($coach);
    $generation = AiGeneration::query()->create([
        'membre_id' => $member->id,
        'demande_par_coach_id' => $coach->id,
        'contexte_utilise' => ['objectif' => 'Endurance', 'niveau' => 'debutant'],
        'generee_le' => now(),
    ]);
    WorkoutProgrammeGenerator::fake([[
        'titre' => 'Programme incomplet',
        'sessions' => [[
            'jour' => 'Lundi',
            'notes' => 'Cardio.',
            'exercices' => [],
        ]],
    ]])->preventStrayPrompts();

    (new GenerateWorkoutProgrammeDraft($generation->id))->handle(
        app(WorkoutProgrammeGenerator::class),
        app(WorkoutProgrammeDraftValidator::class),
    );

    $this->assertDatabaseHas('ai_generations', [
        'id' => $generation->id,
        'statut' => 'echec',
        'reponse_brute->error_code' => 'invalid_response',
    ]);
    $this->assertDatabaseCount('programmes', 0);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->getJson("/api/coach/ai-generations/{$generation->id}")
        ->assertOk()
        ->assertJsonPath('data.statut', 'echec')
        ->assertJsonPath('data.error_code', 'invalid_response')
        ->assertJsonPath('data.error', 'Generated programme is incomplete: at least one exercise or cardio recommendation at session 1.');

    $otherCoach = createAiCoach();
    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->getJson("/api/coach/ai-generations/{$generation->id}")->assertForbidden();
});
