# Coach Programme Review Specification

## Requirement: Coach edits a programme draft

The system SHALL allow only the member's assigned coach to retrieve and update a draft programme.

### Scenario: Replace a generated programme draft

- **WHEN** the assigned coach submits a valid replacement set of sessions and exercises for a draft
- **THEN** the system replaces its sessions and exercise details in the submitted order
- **AND THEN** the programme remains a draft.

### Scenario: Edit a non-draft programme

- **WHEN** a coach tries to edit a validated or published programme
- **THEN** the system returns `422 Unprocessable Entity`.

## Requirement: Coach validation and publication

The system SHALL record the assigned coach and timestamp when a draft programme is validated.

### Scenario: Validate and publish a reviewed programme

- **WHEN** the assigned coach validates a draft
- **THEN** the system changes its status to `valide` and records the coach and current validation date.
- **WHEN** the assigned coach publishes that validated programme
- **THEN** the system changes its status to `publie`.

### Scenario: Publish without validation

- **WHEN** a coach attempts to publish a draft
- **THEN** the system returns `422 Unprocessable Entity` and retains the `brouillon` status.
