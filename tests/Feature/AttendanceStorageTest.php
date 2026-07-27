<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores one attendance record per member and date', function () {
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member'])->id]);
    Attendance::query()->create(['membre_id' => $member->id, 'date_presence' => today(), 'enregistre_le' => now()]);

    expect(Attendance::query()
        ->where('membre_id', $member->id)
        ->whereDate('date_presence', today())
        ->exists())
        ->toBeTrue();
});
