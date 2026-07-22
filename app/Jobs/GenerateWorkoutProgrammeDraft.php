<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateWorkoutProgrammeDraft implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $generationId) {}

    public function handle(): void
    {
        // Structured output persistence is added in GFRS-42.
    }
}
