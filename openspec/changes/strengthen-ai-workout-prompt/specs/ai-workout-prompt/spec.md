# AI Workout Prompt Specification

## Requirement: Prompt includes complete member and coach context

The system SHALL create a generation prompt containing the member objective, level, weight, height, injuries, available days, preferences, recent programme history, and coach constraints.

### Scenario: Member has an injury

- **WHEN** a profile includes an injury or restriction
- **THEN** the prompt instructs the AI to avoid aggravating movements, use conservative alternatives, and not provide medical advice.

### Scenario: Generate a coach-review draft

- **WHEN** the generation prompt is created
- **THEN** it explicitly identifies the result as a draft requiring coach review, editing, validation, and publication.
