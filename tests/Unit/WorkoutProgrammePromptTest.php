<?php

use App\Ai\Prompts\WorkoutProgrammePrompt;

it('builds a safe weekly coach-review prompt from all profile and coach constraints', function () {
    $prompt = WorkoutProgrammePrompt::for(WorkoutProgrammePrompt::context([
        'objectif' => 'Prise de masse',
        'niveau' => 'intermediaire',
        'poids' => 78.5,
        'taille' => 180,
        'blessures' => 'Ancienne douleur au genou',
        'jours_disponibles' => ['lundi', 'mercredi', 'vendredi'],
        'preferences' => 'Poids libres',
        'historique_programmes' => [['titre' => 'Programme precedent', 'statut' => 'publie']],
        'coach_constraints' => [
            'specialite' => 'Musculation',
            'disponibilite' => 'Lundi au vendredi',
        ],
    ]));

    expect($prompt)
        ->toContain('Prise de masse')
        ->toContain('intermediaire')
        ->toContain('78.5')
        ->toContain('180')
        ->toContain('Ancienne douleur au genou')
        ->toContain('lundi')
        ->toContain('Poids libres')
        ->toContain('Programme precedent')
        ->toContain('Musculation')
        ->toContain('exactly 2 exercises in every session')
        ->toContain('Do not diagnose, treat, or provide medical advice')
        ->toContain('DRAFT for coach review');
});

it('adapts the exercise count to level and constraints', function () {
    expect(WorkoutProgrammePrompt::exerciseCountFor(['niveau' => 'debutant', 'blessures' => 'Aucune']))->toBe(2)
        ->and(WorkoutProgrammePrompt::exerciseCountFor(['niveau' => 'intermediaire', 'blessures' => 'Aucune']))->toBe(3)
        ->and(WorkoutProgrammePrompt::exerciseCountFor(['niveau' => 'avance', 'blessures' => 'Aucune']))->toBe(4)
        ->and(WorkoutProgrammePrompt::exerciseCountFor(['niveau' => 'avance', 'blessures' => 'Douleur a l epaule']))->toBe(2);
});
