# Member Programmes Specification

## Requirement: Member views the current published programme

The system SHALL allow an authenticated member to retrieve only that member's currently active published programme.

### Scenario: View the current plan by training day

- **WHEN** a member has a published programme active on the current date
- **THEN** the system returns the programme's sessions in order
- **AND THEN** each session includes exercise sets, repetitions, rest, cardio duration, and coach notes.

### Scenario: Access a draft or another member's programme

- **WHEN** a member requests a programme that is not their own published programme
- **THEN** the system returns `404 Not Found`.
