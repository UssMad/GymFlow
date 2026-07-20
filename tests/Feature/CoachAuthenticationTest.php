<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('issues a bearer token for valid coach credentials', function () {
    $coach = User::factory()->create([
        'role' => 'coach',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/coach/login', [
        'email' => $coach->email,
        'password' => 'secret-password',
    ])
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $coach->id)
        ->assertJsonPath('user.role', 'coach');

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('does not issue a coach token to another role', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/coach/login', [
        'email' => $admin->email,
        'password' => 'secret-password',
    ])->assertForbidden();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('requires a bearer token for coach endpoints', function () {
    $this->getJson('/api/coach/me')->assertUnauthorized();
});

it('returns the authenticated coach from the protected endpoint', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    Sanctum::actingAs($coach, ['coach']);

    $this->getJson('/api/coach/me')
        ->assertOk()
        ->assertJsonPath('data.id', $coach->id)
        ->assertJsonPath('data.role', 'coach');
});
