<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('lets an admin sign in through the web interface and redirects to their dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('secret-password'),
    ]);

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('dashboard'));

    $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
});

it('does not allow a guest to open a role dashboard', function () {
    $this->get('/coach/dashboard')->assertRedirect(route('login'));
});

it('signs a user out of the web interface', function () {
    $user = User::factory()->create(['role' => 'member']);

    $this->actingAs($user)->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
