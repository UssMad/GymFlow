# Validate AI workout response before persistence

## Why

Structured-output support reduces malformed responses, but GymFlow must still independently verify data before creating a programme or exercises.

## What changes

- Validate required training sessions and exercise prescriptions before the database transaction.
- Reject missing sessions, missing exercises, invalid types, and incomplete cardio or strength prescriptions.
- Store a coach-readable failure state without creating a programme.
