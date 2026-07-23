# Coach Member Progress Specification

## Requirement: Aggregate member progress

The system SHALL aggregate each member's total sessions, completed sessions, completion rate, latest completion, difficulty counts, and recent completed-session feedback.

### Scenario: Query one member's progress

- **WHEN** the progress service is called for a member
- **THEN** it returns only sessions belonging to that member and excludes all other members' sessions.
