<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('issues a bearer token for valid admin credentials', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/admin/login', [
        'email' => $admin->email,
        'password' => 'secret-password',
    ])
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $admin->id)
        ->assertJsonPath('user.role', 'admin');

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('does not issue an admin token to another role', function () {
    $coach = User::factory()->create([
        'role' => 'coach',
        'password' => Hash::make('secret-password'),
    ]);

    $this->postJson('/api/admin/login', [
        'email' => $coach->email,
        'password' => 'secret-password',
    ])->assertForbidden();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('requires a bearer token for admin endpoints', function () {
    $this->getJson('/api/admin/me')->assertUnauthorized();
});

it('returns the authenticated admin from the protected endpoint', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($admin, ['admin']);

    $this->getJson('/api/admin/me')
        ->assertOk()
        ->assertJsonPath('data.id', $admin->id)
        ->assertJsonPath('data.role', 'admin');
});
