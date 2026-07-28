<?php

use App\Ai\Agents\WorkoutProgrammeGenerator;
use Laravel\Ai\Gateway\TextGenerationOptions;

it('defines a structured weekly draft contract and keeps approval under coach control', function () {
    $generator = app(WorkoutProgrammeGenerator::class);
    $instructions = (string) $generator->instructions();

    expect($instructions)
        ->toContain('title and exactly three ordered weekly sessions')
        ->toContain('name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression')
        ->toContain('Do not return a programme status, validation decision, or publication decision');

    expect(TextGenerationOptions::forAgent($generator)->maxTokens)->toBe(1200);
});
