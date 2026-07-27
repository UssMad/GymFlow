<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a coach their members and programmes awaiting review', function () {
    $coach = Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Monday to Friday',
    ]);
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);
    Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Foundation strength',
        'source' => 'manuel',
        'statut' => 'brouillon',
    ]);

    $this->actingAs($coach->user)->get('/coach/dashboard')
        ->assertOk()
        ->assertSee($member->user->prenom)
        ->assertSee('Foundation strength')
        ->assertSee('Consistency board');
});

it('does not allow a member to access the coach dashboard', function () {
    $member = User::factory()->create(['role' => 'member']);

    $this->actingAs($member)->get('/coach/dashboard')->assertForbidden();
});
