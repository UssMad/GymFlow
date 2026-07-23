# Manual workout programme creation

## Why

Coaches need a reliable alternative when an AI-generated draft is not appropriate.

## What changes

- Reuse the existing `programmes`, `workout_sessions`, `exercises`, and `exercise_details` schema.
- Provide a REST representation of a programme and its nested sessions and exercises.
- Add a coach-only endpoint to create a manual weekly draft for an assigned member.

## Out of scope

- Publishing, member progress tracking, payments, and AI generation changes.
