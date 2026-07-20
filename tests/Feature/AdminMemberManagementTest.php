<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('requires an authenticated admin to manage members', function () {
    $this->getJson('/api/admin/members')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create(['role' => 'coach']), ['coach']);

    $this->getJson('/api/admin/members')->assertForbidden();
});

it('creates a member account and an associated member record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coachUser = User::factory()->create(['role' => 'coach']);
    $coach = Coach::query()->create([
        'user_id' => $coachUser->id,
        'specialite' => 'Musculation',
        'disponibilite' => 'Lundi au vendredi',
    ]);

    Sanctum::actingAs($admin, ['admin']);

    $this->postJson('/api/admin/members', [
        'nom' => 'El Amrani',
        'prenom' => 'Sara',
        'email' => 'sara@example.test',
        'password' => 'very-secure-password',
        'password_confirmation' => 'very-secure-password',
        'coach_id' => $coach->id,
        'statut_abonnement' => 'actif',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'sara@example.test')
        ->assertJsonPath('data.user.role', 'member')
        ->assertJsonPath('data.coach.id', $coach->id);

    $user = User::query()->where('email', 'sara@example.test')->firstOrFail();

    expect($user->role)->toBe('member');
    $this->assertDatabaseHas('members', ['user_id' => $user->id, 'coach_id' => $coach->id]);
});

it('lists and updates members without allowing a role change', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $memberUser = User::factory()->create(['role' => 'member']);
    $member = Member::query()->create(['user_id' => $memberUser->id]);

    Sanctum::actingAs($admin, ['admin']);

    $this->getJson('/api/admin/members')
        ->assertOk()
        ->assertJsonPath('data.0.id', $member->id);

    $this->patchJson("/api/admin/members/{$member->id}", [
        'prenom' => 'Updated',
        'statut_abonnement' => 'suspendu',
        'role' => 'admin',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.prenom', 'Updated')
        ->assertJsonPath('data.user.role', 'member')
        ->assertJsonPath('data.statut_abonnement', 'suspendu');
});

it('validates member creation fields', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']), ['admin']);

    $this->postJson('/api/admin/members', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nom', 'prenom', 'email', 'password']);
});
