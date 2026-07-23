# Member Workout Completion Specification

## Requirement: Store workout completion

The system SHALL store a workout session's completion status, completion timestamp, member feedback, and perceived difficulty.

### Scenario: Member completes a session

- **WHEN** a member marks an eligible session as completed
- **THEN** the system stores status `realise`, the current completion timestamp, optional feedback, and optional difficulty.

### Scenario: Protect unpublished and foreign sessions

- **WHEN** a member tries to complete a session from a draft programme or another member's programme
- **THEN** the system returns `404 Not Found` and does not change the session.
