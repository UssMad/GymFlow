<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the administrator a member dashboard and records attendance once per day', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'statut_abonnement' => 'actif',
    ]);

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertOk()
        ->assertSee($member->user->email)
        ->assertSee('Today’s check-in');

    $this->actingAs($admin)->post(route('admin.attendance.store', $member))
        ->assertSessionHas('status');

    expect(Attendance::query()->where('membre_id', $member->id)->count())->toBe(1);
});

it('does not allow a coach to access the administrator dashboard', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $this->actingAs($coach)->get('/admin/dashboard')->assertForbidden();
});
