## ADDED Requirements

### Requirement: Assigned coaches can queue programme generation
The system SHALL let only an assigned coach queue AI generation for a member who has a sports profile.

#### Scenario: Valid request
- **WHEN** an assigned coach requests generation
- **THEN** the system records objective, level, injuries, available days, preferences, and recent programme history
- **AND** responds with HTTP 202.

### Requirement: Generated programmes remain drafts
The system SHALL store successful structured output as an auditable AI generation and a programme with status `brouillon` and source `ia`.

#### Scenario: Successful generation
- **WHEN** the queued job receives valid structured output
- **THEN** the system creates a linked draft programme
- **AND** does not publish it.

### Requirement: Failures are traceable
The system SHALL retain the generation context and mark the generation `echec` when the provider fails.
