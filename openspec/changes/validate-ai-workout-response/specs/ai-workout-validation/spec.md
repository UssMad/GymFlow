# AI Workout Response Validation Specification

## Requirement: Validate a generated programme before saving

The system SHALL validate the generated response before it creates a programme, session, exercise, or exercise detail.

### Scenario: Valid weekly programme

- **WHEN** the response contains a title, one or more training days, and a valid exercise or cardio recommendation in each day
- **THEN** the system may save it as an AI programme draft.

### Scenario: Incomplete weekly programme

- **WHEN** the response is missing training days or a session has no exercise or cardio recommendation
- **THEN** the system marks the generation as failed and persists no programme.

## Requirement: Coach sees a generation failure

The system SHALL provide the assigned coach with a clear failure message when a generation cannot be saved.

### Scenario: Invalid response message

- **WHEN** the generated response is invalid
- **THEN** the assigned coach can retrieve the generation status and a clear validation message
- **AND THEN** another coach cannot retrieve that generation.
