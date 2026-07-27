<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createAttendanceMember(): Member
{
    return Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);
}

it('lets an admin record a member check-in and prevents non-admin access', function () {
    $member = createAttendanceMember();
    $this->postJson("/api/admin/members/{$member->id}/attendances", [])->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create(['role' => 'coach']), ['coach']);
    $this->getJson('/api/admin/attendances')->assertForbidden();

    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin, ['admin']);

    $this->postJson("/api/admin/members/{$member->id}/attendances", [
        'date_presence' => today()->toDateString(),
        'notes' => 'Check-in du matin.',
    ])->assertCreated()
        ->assertJsonPath('data.membre_id', $member->id)
        ->assertJsonPath('data.notes', 'Check-in du matin.');
});

it('lists attendance history filtered by member and date range', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = createAttendanceMember();
    $otherMember = createAttendanceMember();
    Attendance::query()->create(['membre_id' => $member->id, 'date_presence' => today()->subDay(), 'enregistre_le' => now()]);
    Attendance::query()->create(['membre_id' => $member->id, 'date_presence' => today()->subDays(8), 'enregistre_le' => now()]);
    Attendance::query()->create(['membre_id' => $otherMember->id, 'date_presence' => today()->subDay(), 'enregistre_le' => now()]);

    Sanctum::actingAs($admin, ['admin']);

    $this->getJson('/api/admin/attendances?membre_id='.$member->id.'&date_debut='.today()->subDays(2)->toDateString().'&date_fin='.today()->toDateString())
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.membre_id', $member->id);
});
