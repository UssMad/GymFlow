<?php

use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCoachForMemberWorkflow(): Coach
{
    return Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Weekdays',
    ]);
}

it('lets a coach manage an assigned member sport profile', function () {
    $coach = makeCoachForMemberWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $coach->id,
    ]);

    $this->actingAs($coach->user)
        ->get(route('coach.members.show', $member))
        ->assertOk()
        ->assertSee('Sport profile');

    $this->actingAs($coach->user)
        ->put(route('coach.members.sport-profile.update', $member), [
            'objectif' => 'Build strength',
            'niveau' => 'intermediaire',
            'poids' => 72.5,
            'taille' => 178,
            'jours_disponibles' => ['monday', 'wednesday'],
            'preferences' => 'Free weights',
        ])
        ->assertSessionHas('status', 'Sport profile saved.');

    $this->assertDatabaseHas('sport_profiles', [
        'membre_id' => $member->id,
        'objectif' => 'Build strength',
    ]);

});

it('does not let a coach manage another coach member', function () {
    $coach = makeCoachForMemberWorkflow();
    $otherCoach = makeCoachForMemberWorkflow();
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
        'coach_id' => $otherCoach->id,
    ]);

    $this->actingAs($coach->user)->get(route('coach.members.show', $member))->assertForbidden();
});
