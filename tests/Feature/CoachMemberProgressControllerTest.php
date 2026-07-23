<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createProgressCoach(): Coach
{
    $user = User::factory()->create(['role' => 'coach']);

    return Coach::query()->create([
        'user_id' => $user->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);
}

function createProgressMember(Coach $coach): Member
{
    return Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
}

it('lets a coach view progress and member feedback only for assigned members', function () {
    $coach = createProgressCoach();
    $member = createProgressMember($coach);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Programme force',
        'source' => 'manuel',
        'statut' => 'publie',
    ]);
    $programme->sessions()->create([
        'jour' => 'Lundi',
        'ordre' => 1,
        'statut' => 'realise',
        'realisee_le' => now(),
        'difficulte_ressentie' => 'difficile',
        'retour_membre' => 'Besoin de plus de repos.',
    ]);

    Sanctum::actingAs($coach->user, ['coach']);

    $this->getJson("/api/coach/members/{$member->id}/progress")
        ->assertOk()
        ->assertJsonPath('data.member.id', $member->id)
        ->assertJsonPath('data.summary.completed_sessions', 1)
        ->assertJsonPath('data.summary.difficulty.difficile', 1)
        ->assertJsonPath('data.recent_completed_sessions.0.retour_membre', 'Besoin de plus de repos.');

    $otherCoach = createProgressCoach();
    Sanctum::actingAs($otherCoach->user, ['coach']);

    $this->getJson("/api/coach/members/{$member->id}/progress")->assertForbidden();
});
