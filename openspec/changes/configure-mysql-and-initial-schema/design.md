# Design

## Connection

Laravel uses its built-in `mysql` connection. Real credentials remain only in
the ignored `.env` file; `.env.example` has safe local defaults.

## Domain schema

- `users` contains authentication identities and a role (`admin`, `coach`, or
  `member`).
- `coaches` and `members` extend `users` through one-to-one relationships.
- A member can have no coach or one coach; a coach can follow many members.
- A member has zero or one `sport_profiles` record.
- `ai_generations` is an auditable generation attempt, not the programme.
- A programme may be manual or come from one AI generation. A coach validates
  it before it can be published.
- `workout_sessions` is used for training sessions so it never conflicts with
  Laravel's `sessions` table.

## Integrity and performance

Owned records cascade on deletion. Optional historical references use
`nullOnDelete`. Unique and composite indexes protect one-to-one links and the
order of sessions inside a programme.
