<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters attendance history by member and date range in the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $member = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member', 'prenom' => 'Sara'])->id]);
    $otherMember = Member::query()->create(['user_id' => User::factory()->create(['role' => 'member', 'prenom' => 'Amine'])->id]);
    Attendance::query()->create(['membre_id' => $member->id, 'date_presence' => today()->subDay(), 'enregistre_le' => now()]);
    Attendance::query()->create(['membre_id' => $member->id, 'date_presence' => today()->subDays(8), 'enregistre_le' => now()]);
    Attendance::query()->create(['membre_id' => $otherMember->id, 'date_presence' => today()->subDay(), 'enregistre_le' => now()]);

    $response = $this->actingAs($admin)->get('/admin/dashboard?membre_id='.$member->id.'&date_debut='.today()->subDays(2)->toDateString().'&date_fin='.today()->toDateString())
        ->assertOk()
        ->assertSee('Sara');

    preg_match_all('/<div class="attendance-item">.*?<strong>(.*?)<\/strong>/s', $response->getContent(), $matches);

    expect($matches[1])->toBe([$member->user->prenom.' '.$member->user->nom]);
});
