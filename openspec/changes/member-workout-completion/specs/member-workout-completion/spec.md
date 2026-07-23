# Member Workout Completion Specification

## Requirement: Store workout completion

The system SHALL store a workout session's completion status, completion timestamp, member feedback, and perceived difficulty.

### Scenario: Member completes a session

- **WHEN** a member marks an eligible session as completed
- **THEN** the system stores status `realise`, the current completion timestamp, optional feedback, and optional difficulty.
