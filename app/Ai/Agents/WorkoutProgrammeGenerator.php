<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(2200)]
#[Timeout(180)]
class WorkoutProgrammeGenerator implements Agent, HasProviderOptions
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are GymFlow's fitness-programme assistant. Generate a safe weekly workout draft from the supplied member profile and coach constraints. Respect injuries and constraints. Do not give medical advice. This is for coach review and must never be presented as published.

            Return only valid JSON. Do not use Markdown, code fences, or explanatory text. Use these exact JSON keys:
            - A title and exactly three ordered weekly sessions. The member prompt states the exact number of exercises required in every session.
            - Root keys: titre, sessions.
            - Session keys: jour, notes, exercices.
            - Exercise keys: nom, groupe_musculaire, type, series, repetitions, repos, duree_cardio, notes, progression.
            - Every session has a training day, notes, and one or more exercises.
            - Every exercise includes its name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression.
            - For strength work, use meaningful sets, repetitions, and rest. For cardio or mobility, use cardio duration when relevant; use 0 only for values that do not apply.
            - Do not return a programme status, validation decision, or publication decision. GymFlow always saves your result as a draft and the coach controls validation and publication.
            INSTRUCTIONS;
    }

    public function providerOptions(Lab|string $provider): array
    {
        if ($provider === Lab::OpenRouter || $provider === 'openrouter') {
            return ['reasoning' => ['effort' => 'low', 'exclude' => true]];
        }

        return [];
    }

}
