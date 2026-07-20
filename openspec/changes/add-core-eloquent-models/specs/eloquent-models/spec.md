## ADDED Requirements

### Requirement: Core domain models
The project MUST provide Eloquent models for every GymFlow domain table created
by the initial schema migration.

#### Scenario: Laravel loads a GymFlow relationship
- **WHEN** application code accesses a member's coach, profile, programmes, or
  AI generations
- **THEN** Eloquent resolves the matching relationship with the MLD foreign keys.

### Requirement: AI and programme separation
The `AiGeneration` model MUST remain separate from `Programme`, with a
generation having at most one resulting programme.

#### Scenario: A manual programme is created
- **WHEN** a coach creates a manual programme
- **THEN** the programme can exist without an AI generation relationship.

### Requirement: Session model naming
The training-session model MUST map to `workout_sessions`.

#### Scenario: Laravel resolves a programme's sessions
- **WHEN** application code accesses `Programme::sessions()`
- **THEN** it returns `WorkoutSession` records and never Laravel's internal
  `sessions` records.
