<?php

namespace App\Ai\Validators;

use InvalidArgumentException;

class WorkoutProgrammeDraftValidator
{
    public function validate(array $draft): void
    {
        $this->requireText($draft['titre'] ?? null, 'a programme title');

        $sessions = $draft['sessions'] ?? null;
        if (! is_array($sessions) || $sessions === []) {
            $this->fail('at least one training day');
        }

        foreach ($sessions as $sessionIndex => $session) {
            if (! is_array($session)) {
                $this->fail('a valid training day at session '.($sessionIndex + 1));
            }

            $this->requireText($session['jour'] ?? null, 'a training day at session '.($sessionIndex + 1));
            $this->requireText($session['notes'] ?? null, 'coach notes at session '.($sessionIndex + 1));

            $exercises = $session['exercices'] ?? null;
            if (! is_array($exercises) || $exercises === []) {
                $this->fail('at least one exercise or cardio recommendation at session '.($sessionIndex + 1));
            }

            foreach ($exercises as $exerciseIndex => $exercise) {
                if (! is_array($exercise)) {
                    $this->fail('a valid exercise at session '.($sessionIndex + 1).', exercise '.($exerciseIndex + 1));
                }

                $this->validateExercise($exercise, $sessionIndex, $exerciseIndex);
            }
        }
    }

    private function validateExercise(array $exercise, int $sessionIndex, int $exerciseIndex): void
    {
        $label = 'at session '.($sessionIndex + 1).', exercise '.($exerciseIndex + 1);
        $this->requireText($exercise['nom'] ?? null, 'an exercise name '.$label);
        $this->requireText($exercise['groupe_musculaire'] ?? null, 'a muscle group '.$label);
        $this->requireText($exercise['repos'] ?? null, 'a rest instruction '.$label);
        $this->requireText($exercise['notes'] ?? null, 'exercise notes '.$label);
        $this->requireText($exercise['progression'] ?? null, 'a progression note '.$label);

        $type = $exercise['type'] ?? null;
        if (! is_string($type) || ! in_array($type, ['musculation', 'cardio', 'mobilite'], true)) {
            $this->fail('a supported exercise type '.$label);
        }

        $this->requireNonNegativeInteger($exercise['series'] ?? null, 'a set count '.$label);
        $this->requireNonNegativeInteger($exercise['repetitions'] ?? null, 'a repetition count '.$label);
        $this->requireNonNegativeInteger($exercise['duree_cardio'] ?? null, 'a cardio duration '.$label);

        if ($type === 'musculation' && ($exercise['series'] < 1 || $exercise['repetitions'] < 1)) {
            $this->fail('positive sets and repetitions for strength work '.$label);
        }

        if ($type === 'cardio' && $exercise['duree_cardio'] < 1) {
            $this->fail('a positive cardio duration for cardio work '.$label);
        }
    }

    private function requireText(mixed $value, string $field): void
    {
        if (! is_string($value) || trim($value) === '') {
            $this->fail($field);
        }
    }

    private function requireNonNegativeInteger(mixed $value, string $field): void
    {
        if (! is_int($value) || $value < 0) {
            $this->fail($field);
        }
    }

    private function fail(string $missing): never
    {
        throw new InvalidArgumentException('Generated programme is incomplete: '.$missing.'.');
    }
}
