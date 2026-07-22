<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class WorkoutProgrammeGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are GymFlow\'s fitness-programme assistant. Generate a safe weekly workout draft from the supplied member profile. Respect injuries and constraints. Do not give medical advice. This is for coach review and must never be presented as published.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'titre' => $schema->string()->required(),
            'sessions' => $schema->array()->items($schema->object(fn (JsonSchema $schema): array => [
                'jour' => $schema->string()->required(),
                'notes' => $schema->string()->required(),
                'exercices' => $schema->array()->items($schema->object(fn (JsonSchema $schema): array => [
                    'nom' => $schema->string()->required(),
                    'groupe_musculaire' => $schema->string()->required(),
                    'type' => $schema->string()->enum(['musculation', 'cardio', 'mobilite'])->required(),
                    'series' => $schema->integer()->min(0)->required(),
                    'repetitions' => $schema->integer()->min(0)->required(),
                    'repos' => $schema->string()->required(),
                    'duree_cardio' => $schema->integer()->min(0)->required(),
                    'notes' => $schema->string()->required(),
                    'progression' => $schema->string()->required(),
                ]))->min(1)->required(),
            ]))->min(1)->required(),
        ];
    }
}
