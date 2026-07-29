<?php

namespace App\Services;

use App\Ai\Prompts\WorkoutProgrammePrompt;

class WorkoutProgrammeFallbackFactory
{
    /** @return array<string, mixed> */
    public function make(array $context): array
    {
        $days = array_values(array_filter((array) ($context['jours_disponibles'] ?? [])));
        $days = array_pad($days, 3, 'training day');
        $exerciseCount = WorkoutProgrammePrompt::exerciseCountFor($context);
        $sessions = [
            ['notes' => 'Low-impact cardio and lower-body strength. Keep movements pain-free.', 'exercices' => [
                $this->exercise('Tapis roulant - marche rapide', 'Cardio', 'cardio', 1, 0, 0, 25, 'Maintain a comfortable pace.', 'Add 2 minutes next week.'),
                $this->exercise('Squat sans charge', 'Jambes', 'musculation', 3, 12, 75, 0, 'Keep the chest lifted and knees aligned.', 'Add 1 repetition per set when comfortable.'),
                $this->exercise('Pont fessier', 'Fessiers', 'musculation', 3, 12, 60, 0, 'Keep the hips level and control the movement.', 'Add 2 repetitions next week.'),
                $this->exercise('Step-up sur marche basse', 'Jambes', 'musculation', 3, 10, 60, 0, 'Use a stable step and avoid pain.', 'Increase to 12 repetitions.'),
                $this->exercise('Etirement des mollets', 'Mobilite', 'mobilite', 2, 0, 30, 0, 'Hold gently without bouncing.', 'Hold 5 seconds longer next week.'),
            ]],
            ['notes' => 'Controlled lower-body and core work with shoulder-friendly conditioning.', 'exercices' => [
                $this->exercise('Velo stationnaire', 'Cardio', 'cardio', 1, 0, 0, 20, 'Use a steady, moderate effort.', 'Add 3 minutes next week.'),
                $this->exercise('Fente arriere assistee', 'Jambes', 'musculation', 3, 10, 75, 0, 'Hold a stable support if needed.', 'Add 1 repetition per set.'),
                $this->exercise('Bird dog', 'Tronc', 'mobilite', 3, 10, 60, 0, 'Keep the spine neutral and move slowly.', 'Hold each repetition for 2 seconds longer.'),
                $this->exercise('Mollets debout', 'Mollets', 'musculation', 3, 15, 45, 0, 'Use a wall for balance.', 'Add 2 repetitions next week.'),
                $this->exercise('Mobilite des hanches', 'Mobilite', 'mobilite', 2, 0, 30, 0, 'Use a gentle range of motion.', 'Increase the range gradually.'),
            ]],
            ['notes' => 'Cardio endurance, leg strength, and a gentle recovery finish.', 'exercices' => [
                $this->exercise('Elliptique', 'Cardio', 'cardio', 1, 0, 0, 25, 'Keep resistance light to moderate.', 'Add 2 minutes next week.'),
                $this->exercise('Souleve de terre roumain leger', 'Jambes', 'musculation', 3, 10, 90, 0, 'Keep the back neutral and use a light load.', 'Add a small amount of load only when form is stable.'),
                $this->exercise('Planche sur les genoux', 'Tronc', 'mobilite', 3, 0, 60, 0, 'Hold a comfortable position without shoulder pain.', 'Add 5 seconds per hold next week.'),
                $this->exercise('Chaise contre le mur', 'Jambes', 'musculation', 3, 12, 60, 0, 'Keep knees comfortable and back supported.', 'Increase hold time gradually.'),
                $this->exercise('Etirement des ischio-jambiers', 'Mobilite', 'mobilite', 2, 0, 30, 0, 'Breathe steadily and do not force the stretch.', 'Hold 5 seconds longer next week.'),
            ]],
        ];

        return [
            'titre' => 'Programme hebdomadaire adapte - '.($context['objectif'] ?? 'forme'),
            'sessions' => array_map(
                fn (array $session, int $index): array => [
                    'jour' => $days[$index],
                    'notes' => $session['notes'],
                    'exercices' => array_slice($session['exercices'], 0, $exerciseCount),
                ],
                $sessions,
                array_keys($sessions),
            ),
        ];
    }

    /** @return array<string, int|string> */
    private function exercise(string $name, string $muscleGroup, string $type, int $sets, int $repetitions, int $rest, int $cardioDuration, string $notes, string $progression): array
    {
        return [
            'nom' => $name,
            'groupe_musculaire' => $muscleGroup,
            'type' => $type,
            'series' => $sets,
            'repetitions' => $repetitions,
            'repos' => $rest,
            'duree_cardio' => $cardioDuration,
            'notes' => $notes,
            'progression' => $progression,
        ];
    }
}
