<?php

use App\Models\Coach;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an administrator create and update a coach through Blade forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.coaches.create'))
        ->assertOk()
        ->assertSee('Create a coach account');

    $this->actingAs($admin)->post(route('admin.coaches.store'), [
        'prenom' => 'Amine',
        'nom' => 'Bennani',
        'email' => 'amine.coach@example.test',
        'password' => 'very-secure-password',
        'password_confirmation' => 'very-secure-password',
        'specialite' => 'Strength training',
        'disponibilite' => 'Monday to Friday',
    ])->assertRedirect();

    $coach = Coach::query()->whereHas('user', fn ($query) => $query->where('email', 'amine.coach@example.test'))->firstOrFail();

    $this->actingAs($admin)->put(route('admin.coaches.update', $coach), [
        'prenom' => 'Amine',
        'nom' => 'Bennani',
        'email' => 'amine.coach@example.test',
        'password' => null,
        'password_confirmation' => null,
        'specialite' => 'Mobility and conditioning',
        'disponibilite' => 'Tuesday to Saturday',
    ])->assertSessionHas('status', 'Coach details saved.');

    $this->assertDatabaseHas('users', ['id' => $coach->user_id, 'role' => 'coach']);
    $this->assertDatabaseHas('coaches', ['id' => $coach->id, 'specialite' => 'Mobility and conditioning']);
});

it('does not let a member access coach management', function () {
    $member = User::factory()->create(['role' => 'member']);

    $this->actingAs($member)->get(route('admin.coaches.create'))->assertForbidden();
});
