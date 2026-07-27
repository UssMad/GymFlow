<?php

use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a member their current programme and records a completed workout', function () {
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Momentum programme',
        'source' => 'manuel',
        'statut' => 'publie',
        'date_debut' => today()->subDay(),
        'date_fin' => today()->addDay(),
    ]);
    $session = $programme->sessions()->create(['jour' => 'Monday', 'ordre' => 1]);
    $exercise = Exercise::query()->create([
        'nom' => 'Goblet squat',
        'groupe_musculaire' => 'Legs',
        'type' => 'musculation',
    ]);
    $session->exerciseDetails()->create(['exercice_id' => $exercise->id, 'ordre' => 1, 'series' => 3, 'repetitions' => 10]);

    $this->actingAs($member->user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Momentum programme')
        ->assertSee('Goblet squat');

    $this->actingAs($member->user)->put(route('member.workouts.complete', $session), [
        'difficulte_ressentie' => 'moderee',
        'retour_membre' => 'Good energy today.',
    ])->assertSessionHas('status');

    $this->assertDatabaseHas('workout_sessions', [
        'id' => $session->id,
        'statut' => 'realise',
        'difficulte_ressentie' => 'moderee',
    ]);
});

it('does not allow a coach to use the member dashboard', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $this->actingAs($coach)->get('/member/dashboard')->assertForbidden();
});

it('lets a member mark a planned workout as missed with a reason', function () {
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Recovery week',
        'source' => 'manuel',
        'statut' => 'publie',
    ]);
    $session = $programme->sessions()->create(['jour' => 'Thursday', 'ordre' => 1]);

    $this->actingAs($member->user)
        ->put(route('member.workouts.missed', $session), ['raison_non_realisation' => 'Knee discomfort.'])
        ->assertSessionHas('status', 'Thursday is marked as missed.');

    $this->assertDatabaseHas('workout_sessions', [
        'id' => $session->id,
        'statut' => 'non_realise',
        'raison_non_realisation' => 'Knee discomfort.',
    ]);
});
