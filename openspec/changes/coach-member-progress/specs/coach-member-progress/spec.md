# Coach Member Progress Specification

## Requirement: Aggregate member progress

The system SHALL aggregate each member's total sessions, completed sessions, completion rate, latest completion, difficulty counts, and recent completed-session feedback.

### Scenario: Query one member's progress

- **WHEN** the progress service is called for a member
- **THEN** it returns only sessions belonging to that member and excludes all other members' sessions.

## Requirement: Coach views an assigned member's progress

The system SHALL allow a coach to retrieve progress data only for members assigned to that coach.

### Scenario: Dashboard response

- **WHEN** an assigned coach requests member progress
- **THEN** the system returns completion metrics, difficulty counts, and recent member feedback.

### Scenario: Foreign member

- **WHEN** another coach requests the member's progress
- **THEN** the system returns `403 Forbidden`.
