# Manual Workout Programme Specification

## Requirement: Programme data structure

The system SHALL represent a weekly programme with `programmes`, `workout_sessions`, `exercises`, and `exercise_details`.

### Scenario: Return a programme hierarchy

- **WHEN** a programme is loaded with sessions, details, and exercises
- **THEN** its API representation includes its sessions and the exercises assigned to each session.

## Requirement: Coach creates a manual programme draft

The system SHALL allow an authenticated coach to create a manual weekly programme for a member assigned to that coach.

### Scenario: Create a valid programme

- **WHEN** the coach submits a title, one or more sessions, and an existing exercise for each session
- **THEN** the system creates the programme with source `manuel` and status `brouillon`
- **AND THEN** it persists the sessions and exercise details in their submitted order.

### Scenario: Access another coach's member

- **WHEN** a coach submits a programme for a member assigned to another coach
- **THEN** the system returns `403 Forbidden` and persists nothing.
