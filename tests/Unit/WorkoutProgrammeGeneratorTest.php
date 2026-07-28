<?php

use App\Ai\Agents\WorkoutProgrammeGenerator;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Gateway\TextGenerationOptions;

it('defines a structured weekly draft contract and keeps approval under coach control', function () {
    $generator = app(WorkoutProgrammeGenerator::class);
    $instructions = (string) $generator->instructions();

    expect($instructions)
        ->toContain('Return only valid JSON')
        ->toContain('title and exactly three ordered weekly sessions, with exactly one exercise per session')
        ->toContain('name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression')
        ->toContain('Do not return a programme status, validation decision, or publication decision');

    expect(TextGenerationOptions::forAgent($generator)->maxTokens)->toBe(800);
    expect($generator->providerOptions(Lab::OpenRouter))->toBe(['reasoning' => ['effort' => 'low', 'exclude' => true]]);
});
