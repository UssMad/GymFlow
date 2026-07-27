<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an administrator create and update a member through Blade forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Monday to Friday',
    ]);

    $this->actingAs($admin)->get('/admin/members/create')
        ->assertOk()
        ->assertSee('Create a member account');

    $this->actingAs($admin)->post('/admin/members', [
        'prenom' => 'Sara',
        'nom' => 'El Amrani',
        'email' => 'sara@example.test',
        'password' => 'very-secure-password',
        'password_confirmation' => 'very-secure-password',
        'coach_id' => $coach->id,
        'date_inscription' => today()->toDateString(),
        'statut_abonnement' => 'actif',
    ])->assertRedirect();

    $member = Member::query()->whereHas('user', fn ($query) => $query->where('email', 'sara@example.test'))->firstOrFail();

    $this->actingAs($admin)->put("/admin/members/{$member->id}", [
        'prenom' => 'Sarah',
        'nom' => 'El Amrani',
        'email' => 'sara@example.test',
        'password' => null,
        'password_confirmation' => null,
        'coach_id' => null,
        'date_inscription' => today()->toDateString(),
        'statut_abonnement' => 'suspendu',
    ])->assertSessionHas('status');

    $this->assertDatabaseHas('users', ['id' => $member->user_id, 'prenom' => 'Sarah']);
    $this->assertDatabaseHas('members', ['id' => $member->id, 'coach_id' => null, 'statut_abonnement' => 'suspendu']);
});
