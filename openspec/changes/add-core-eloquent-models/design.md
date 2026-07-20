# Design

## Model naming

The business model names follow the database vocabulary except for
`WorkoutSession`, which maps explicitly to `workout_sessions` and avoids a
collision with Laravel's own session infrastructure.

## Relationships

- `User` has one optional `Member` and one optional `Coach` profile.
- `Coach` belongs to a user, has many members, and validates many programmes.
- `Member` belongs to a user and optional coach; it owns one sport profile and
  has many AI generations and programmes.
- An AI generation has at most one programme; a programme may be manual and
  therefore have no generation.
- A programme contains sessions; sessions contain exercise details; an exercise
  can appear in many details.

## Model responsibilities

Models provide data mapping only: mass-assignment allowlists, casts, defaults,
and relationships. Authorization and business workflows stay in later tasks.
