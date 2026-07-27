<?php

use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createProgrammeMember(): Member
{
    return Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
}

function createMemberProgramme(Member $member, string $status = 'publie', ?string $endDate = null): Programme
{
    $exercise = Exercise::query()->firstOrCreate([
        'nom' => 'Fentes avant',
        'groupe_musculaire' => 'Jambes',
        'type' => 'musculation',
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => "Programme {$status}",
        'source' => 'manuel',
        'statut' => $status,
        'date_debut' => today()->subDay(),
        'date_fin' => $endDate ?? today()->addDay(),
    ]);
    $session = $programme->sessions()->create([
        'jour' => 'Mardi',
        'ordre' => 1,
        'notes' => 'Echauffement avant chaque exercice.',
    ]);
    $session->exerciseDetails()->create([
        'exercice_id' => $exercise->id,
        'ordre' => 1,
        'series' => 4,
        'repetitions' => 12,
        'repos' => '60 secondes',
        'duree_cardio' => 15,
        'notes' => 'Rester controle.',
    ]);

    return $programme;
}

it('authenticates a member and shows the current published programme by training day', function () {
    $member = createProgrammeMember();
    $published = createMemberProgramme($member);
    createMemberProgramme($member, 'brouillon');

    $this->postJson('/api/member/login', [
        'email' => $member->user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('user.role', 'member')
        ->assertJsonStructure(['token']);

    Sanctum::actingAs($member->user, ['member']);

    $this->getJson('/api/member/programmes/current')
        ->assertOk()
        ->assertJsonPath('data.id', $published->id)
        ->assertJsonPath('data.statut', 'publie')
        ->assertJsonPath('data.sessions.0.jour', 'Mardi')
        ->assertJsonPath('data.sessions.0.notes', 'Echauffement avant chaque exercice.')
        ->assertJsonPath('data.sessions.0.exercices.0.series', 4)
        ->assertJsonPath('data.sessions.0.exercices.0.repetitions', 12)
        ->assertJsonPath('data.sessions.0.exercices.0.repos', '60 secondes')
        ->assertJsonPath('data.sessions.0.exercices.0.duree_cardio', 15)
        ->assertJsonPath('data.sessions.0.exercices.0.notes', 'Rester controle.');
});

it('does not expose drafts or another member published programme', function () {
    $member = createProgrammeMember();
    $draft = createMemberProgramme($member, 'brouillon');
    $otherPublished = createMemberProgramme(createProgrammeMember());

    Sanctum::actingAs($member->user, ['member']);

    $this->getJson("/api/member/programmes/{$draft->id}")->assertNotFound();
    $this->getJson("/api/member/programmes/{$otherPublished->id}")->assertNotFound();
});

it('lists only the member past published programmes in history', function () {
    $member = createProgrammeMember();
    $pastPublished = createMemberProgramme($member, 'publie', today()->subDay()->toDateString());
    createMemberProgramme($member, 'brouillon', today()->subDay()->toDateString());
    createMemberProgramme(createProgrammeMember(), 'publie', today()->subDay()->toDateString());

    Sanctum::actingAs($member->user, ['member']);

    $this->getJson('/api/member/programmes/history')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $pastPublished->id)
        ->assertJsonPath('data.0.statut', 'publie');
});
