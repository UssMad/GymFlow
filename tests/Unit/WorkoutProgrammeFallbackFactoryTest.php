<?php

use App\Services\WorkoutProgrammeFallbackFactory;

it('creates a safe three-session fallback with the appropriate exercise volume', function () {
    $draft = (new WorkoutProgrammeFallbackFactory)->make([
        'objectif' => 'Perte de poids',
        'niveau' => 'debutant',
        'blessures' => 'Douleur a l epaule',
        'jours_disponibles' => ['mardi', 'jeudi', 'samedi'],
    ]);

    expect($draft['sessions'])->toHaveCount(3)
        ->and($draft['sessions'][0]['jour'])->toBe('mardi')
        ->and($draft['sessions'][0]['exercices'])->toHaveCount(3)
        ->and($draft['sessions'][2]['exercices'][0]['type'])->toBe('cardio');
});
