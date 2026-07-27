<?php

use App\Models\Member;
use App\Models\Programme;
use App\Models\User;
use App\Services\CoachMemberProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds completion and feedback metrics for one member only', function () {
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Programme progress',
        'source' => 'manuel',
        'statut' => 'publie',
    ]);
    $programme->sessions()->create([
        'jour' => 'Lundi',
        'ordre' => 1,
        'statut' => 'realise',
        'realisee_le' => now()->subDay(),
        'retour_membre' => 'Bonne energie.',
        'difficulte_ressentie' => 'moderee',
    ]);
    $programme->sessions()->create(['jour' => 'Mercredi', 'ordre' => 2]);

    $otherMember = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member'])->id,
    ]);
    $otherProgramme = Programme::query()->create([
        'membre_id' => $otherMember->id,
        'titre' => 'Autre programme',
        'source' => 'manuel',
        'statut' => 'publie',
    ]);
    $otherProgramme->sessions()->create(['jour' => 'Vendredi', 'ordre' => 1, 'statut' => 'realise', 'realisee_le' => now()]);

    $summary = app(CoachMemberProgressService::class)->summary($member);

    expect($summary)
        ->toMatchArray([
            'total_sessions' => 2,
            'completed_sessions' => 1,
            'completion_rate' => 50.0,
            'difficulty' => ['facile' => 0, 'moderee' => 1, 'difficile' => 0],
        ])
        ->and($summary['recent_completed_sessions'])
        ->toHaveCount(1)
        ->and($summary['recent_completed_sessions']->first()->retour_membre)
        ->toBe('Bonne energie.');
});
