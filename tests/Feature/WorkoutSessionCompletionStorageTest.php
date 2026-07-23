<?php

use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a completed workout session with member feedback and difficulty', function () {
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Programme publie',
        'source' => 'manuel',
        'statut' => 'publie',
    ]);
    $session = $programme->sessions()->create(['jour' => 'Lundi', 'ordre' => 1]);

    $session->update([
        'statut' => 'realise',
        'realisee_le' => now(),
        'retour_membre' => 'Bonne seance, effort controle.',
        'difficulte_ressentie' => 'moderee',
    ]);

    $this->assertDatabaseHas('workout_sessions', [
        'id' => $session->id,
        'statut' => 'realise',
        'retour_membre' => 'Bonne seance, effort controle.',
        'difficulte_ressentie' => 'moderee',
    ]);
});
