<?php

use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createCompletionSession(Member $member, string $programmeStatus = 'publie'): WorkoutSession
{
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Programme membre',
        'source' => 'manuel',
        'statut' => $programmeStatus,
    ]);

    return $programme->sessions()->create(['jour' => 'Jeudi', 'ordre' => 1]);
}

function createCompletionMember(): Member
{
    return Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
}

it('lets a member mark an owned published workout session as completed with feedback', function () {
    $member = createCompletionMember();
    $session = createCompletionSession($member);

    Sanctum::actingAs($member->user, ['member']);

    $this->putJson("/api/member/workout-sessions/{$session->id}/completion", [
        'difficulte_ressentie' => 'difficile',
        'retour_membre' => 'Bonne seance, les dernieres repetitions etaient difficiles.',
    ])->assertOk()
        ->assertJsonPath('data.statut', 'realise')
        ->assertJsonPath('data.difficulte_ressentie', 'difficile')
        ->assertJsonPath('data.retour_membre', 'Bonne seance, les dernieres repetitions etaient difficiles.');

    $this->assertDatabaseHas('workout_sessions', [
        'id' => $session->id,
        'statut' => 'realise',
        'difficulte_ressentie' => 'difficile',
    ]);
});

it('does not let a member complete a draft or another member session', function () {
    $member = createCompletionMember();
    $draftSession = createCompletionSession($member, 'brouillon');
    $otherSession = createCompletionSession(createCompletionMember());

    Sanctum::actingAs($member->user, ['member']);

    $this->putJson("/api/member/workout-sessions/{$draftSession->id}/completion")->assertNotFound();
    $this->putJson("/api/member/workout-sessions/{$otherSession->id}/completion")->assertNotFound();
});
