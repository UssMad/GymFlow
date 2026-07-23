# Manual Workout Programme Specification

## Requirement: Programme data structure

The system SHALL represent a weekly programme with `programmes`, `workout_sessions`, `exercises`, and `exercise_details`.

### Scenario: Return a programme hierarchy

- **WHEN** a programme is loaded with sessions, details, and exercises
- **THEN** its API representation includes its sessions and the exercises assigned to each session.
