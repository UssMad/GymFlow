# Change: Add coach-triggered AI programme generation

## Why

Coaches need a traceable way to create a personalised weekly programme draft from a member's sports profile. AI assists the coach; it never publishes a programme automatically.

## What Changes

- Add a queued generation request restricted to the assigned coach.
- Use the official Laravel AI SDK with a structured weekly-workout schema.
- Record the requesting coach and profile context.
- Persist successful results as programme drafts in the next subtask.

## Out of Scope

Payments, medical advice, and automatic publication.
