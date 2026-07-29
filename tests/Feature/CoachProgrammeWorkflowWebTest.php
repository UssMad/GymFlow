<?php

use App\Jobs\GenerateWorkoutProgrammeDraft;
use App\Models\Coach;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\SportProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function makeCoachForProgrammeWorkflow(): Coach
{
    return Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Weekdays',
    ]);
}

it('queues an AI programme for a coached member with a sport profile', function () {
    Queue::fake();
    $coach = makeCoachForProgrammeWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    SportProfile::query()->create([
        'membre_id' => $member->id,
        'objectif' => 'Build strength',
        'niveau' => 'intermediaire',
        'jours_disponibles' => ['monday', 'wednesday'],
        'preferences' => 'Free weights',
    ]);

    $this->actingAs($coach->user)
        ->post(route('coach.members.ai-generations.store', $member))
        ->assertSessionHas('status', 'AI programme generation has been queued for review.');

    Queue::assertPushed(GenerateWorkoutProgrammeDraft::class);
    $this->assertDatabaseHas('ai_generations', [
        'membre_id' => $member->id,
        'statut' => 'en_attente',
    ]);
});

it('lets a coach validate and publish their member draft', function () {
    $coach = makeCoachForProgrammeWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Strength foundation',
        'source' => 'manuel',
        'statut' => 'brouillon',
    ]);

    $this->actingAs($coach->user)->post(route('coach.programmes.validate', $programme))
        ->assertSessionHas('status', 'Programme validated. It is ready to publish.');
    $this->assertDatabaseHas('programmes', ['id' => $programme->id, 'statut' => 'valide', 'coach_validateur_id' => $coach->id]);

    $this->actingAs($coach->user)->post(route('coach.programmes.publish', $programme))
        ->assertSessionHas('status', 'Programme published for the member.');
    $this->assertDatabaseHas('programmes', ['id' => $programme->id, 'statut' => 'publie']);
});

it('lets the assigned coach edit an exercise prescription in a draft only', function () {
    $coach = makeCoachForProgrammeWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Editable draft',
        'source' => 'ia',
        'statut' => 'brouillon',
    ]);
    $detail = $programme->sessions()->create(['jour' => 'Monday', 'ordre' => 1])
        ->exerciseDetails()
        ->create([
            'exercice_id' => Exercise::query()->create([
                'nom' => 'Goblet squat',
                'groupe_musculaire' => 'Legs',
                'type' => 'musculation',
            ])->id,
            'ordre' => 1,
            'series' => 3,
            'repetitions' => 10,
        ]);

    $this->actingAs($coach->user)
        ->put(route('coach.exercise-details.update', $detail), [
            'series' => 4,
            'repetitions' => 12,
            'repos' => '90 seconds',
            'duree_cardio' => 0,
            'notes' => 'Use a controlled tempo.',
        ])
        ->assertSessionHas('status', 'Exercise prescription updated.');

    $this->assertDatabaseHas('exercise_details', [
        'id' => $detail->id,
        'series' => 4,
        'repetitions' => 12,
        'repos' => '90 seconds',
    ]);

    $programme->update(['statut' => 'valide']);

    $this->actingAs($coach->user)
        ->put(route('coach.exercise-details.update', $detail), ['series' => 5])
        ->assertStatus(422);
});

it('lets the assigned coach delete a programme and its workout data', function () {
    $coach = makeCoachForProgrammeWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Programme to delete',
        'source' => 'ia',
        'statut' => 'brouillon',
    ]);
    $session = $programme->sessions()->create(['jour' => 'Monday', 'ordre' => 1]);
    $session->exerciseDetails()->create([
        'exercice_id' => Exercise::query()->create([
            'nom' => 'Bridge',
            'groupe_musculaire' => 'Glutes',
            'type' => 'musculation',
        ])->id,
        'ordre' => 1,
    ]);

    $this->actingAs($coach->user)
        ->delete(route('coach.programmes.destroy', $programme))
        ->assertSessionHas('status', 'Programme deleted.');

    $this->assertDatabaseMissing('programmes', ['id' => $programme->id]);
    $this->assertDatabaseCount('workout_sessions', 0);
    $this->assertDatabaseCount('exercise_details', 0);
});
