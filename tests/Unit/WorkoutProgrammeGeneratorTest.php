<?php

use App\Ai\Agents\WorkoutProgrammeGenerator;

it('defines a structured weekly draft contract and keeps approval under coach control', function () {
    $instructions = (string) app(WorkoutProgrammeGenerator::class)->instructions();

    expect($instructions)
        ->toContain('title and one or more ordered weekly sessions')
        ->toContain('name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression')
        ->toContain('Do not return a programme status, validation decision, or publication decision');
});
