<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows admin navigation links for members and attendance', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Members')
        ->assertSee('Attendance')
        ->assertSee(route('admin.dashboard').'#members', false)
        ->assertSee(route('admin.dashboard').'#attendance', false);
});

it('shows coach navigation links for members and programmes', function () {
    $coach = Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Monday to Friday',
    ]);

    $this->actingAs($coach->user)->get('/coach/dashboard')
        ->assertOk()
        ->assertSee('Members')
        ->assertSee('Programmes')
        ->assertSee(route('coach.members.index'), false)
        ->assertSee(route('coach.programmes.index'), false);
});

it('shows member navigation links for a programme and history', function () {
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);

    $this->actingAs($member->user)->get('/member/dashboard')
        ->assertOk()
        ->assertSee('My programme')
        ->assertSee('History')
        ->assertSee(route('member.dashboard').'#history', false);
});
