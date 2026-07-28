<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(1200)]
class WorkoutProgrammeGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are GymFlow's fitness-programme assistant. Generate a safe weekly workout draft from the supplied member profile and coach constraints. Respect injuries and constraints. Do not give medical advice. This is for coach review and must never be presented as published.

            Return the structured output exactly as requested by the schema:
            - A title and exactly three ordered weekly sessions, with no more than three exercises per session.
            - Every session has a training day, notes, and one or more exercises.
            - Every exercise includes its name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression.
            - For strength work, use meaningful sets, repetitions, and rest. For cardio or mobility, use cardio duration when relevant; use 0 only for values that do not apply.
            - Do not return a programme status, validation decision, or publication decision. GymFlow always saves your result as a draft and the coach controls validation and publication.
            INSTRUCTIONS;
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
