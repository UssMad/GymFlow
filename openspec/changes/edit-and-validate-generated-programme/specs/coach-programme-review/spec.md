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
