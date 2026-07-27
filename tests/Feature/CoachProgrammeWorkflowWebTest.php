<?php

use App\Jobs\GenerateWorkoutProgrammeDraft;
use App\Models\Coach;
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
