<?php

use App\Ai\Validators\WorkoutProgrammeDraftValidator;

function validWorkoutProgrammeDraft(): array
{
    return [
        'titre' => 'Programme hebdomadaire',
        'sessions' => [[
            'jour' => 'Lundi',
            'notes' => 'Haut du corps.',
            'exercices' => [[
                'nom' => 'Tirage horizontal',
                'groupe_musculaire' => 'Dos',
                'type' => 'musculation',
                'series' => 4,
                'repetitions' => 10,
                'repos' => '90 secondes',
                'duree_cardio' => 0,
                'notes' => 'Mouvement controle.',
                'progression' => 'Ajouter une repetition.',
            ]],
        ]],
    ];
}

it('accepts a structured weekly programme with a valid training day and exercise', function () {
    app(WorkoutProgrammeDraftValidator::class)->validate(validWorkoutProgrammeDraft());

    expect(true)->toBeTrue();
});

it('rejects responses without training days or exercise recommendations', function () {
    expect(fn () => app(WorkoutProgrammeDraftValidator::class)->validate(['titre' => 'Sans seance']))
        ->toThrow('Generated programme is incomplete: at least one training day.');

    $withoutExercises = validWorkoutProgrammeDraft();
    $withoutExercises['sessions'][0]['exercices'] = [];

    expect(fn () => app(WorkoutProgrammeDraftValidator::class)->validate($withoutExercises))
        ->toThrow('Generated programme is incomplete: at least one exercise or cardio recommendation at session 1.');
});

it('rejects cardio recommendations without a duration', function () {
    $invalidCardio = validWorkoutProgrammeDraft();
    $invalidCardio['sessions'][0]['exercices'][0]['type'] = 'cardio';
    $invalidCardio['sessions'][0]['exercices'][0]['duree_cardio'] = 0;

    expect(fn () => app(WorkoutProgrammeDraftValidator::class)->validate($invalidCardio))
        ->toThrow('Generated programme is incomplete: a positive cardio duration for cardio work at session 1, exercise 1.');
});
