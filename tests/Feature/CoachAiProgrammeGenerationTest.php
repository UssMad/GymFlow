<?php

use App\Jobs\GenerateWorkoutProgrammeDraft;
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
