# AI Workout Prompt Specification

## Requirement: Prompt includes complete member and coach context

The system SHALL create a generation prompt containing the member objective, level, weight, height, injuries, available days, preferences, recent programme history, and coach constraints.

### Scenario: Member has an injury

- **WHEN** a profile includes an injury or restriction
- **THEN** the prompt instructs the AI to avoid aggravating movements, use conservative alternatives, and not provide medical advice.

### Scenario: Generate a coach-review draft

- **WHEN** the generation prompt is created
- **THEN** it explicitly identifies the result as a draft requiring coach review, editing, validation, and publication.

## Requirement: Structured weekly programme output

The system SHALL require structured output with a programme title, at least one session, and at least one exercise per session.

### Scenario: Output contains an exercise prescription

- **WHEN** the AI returns a weekly draft
- **THEN** every exercise includes name, muscle group, type, sets, repetitions, rest, cardio duration, notes, and progression.

### Scenario: Approval remains coach-controlled

- **WHEN** the AI returns its structured output
- **THEN** it contains no validation or publication decision
- **AND THEN** GymFlow stores the resulting programme as a `brouillon` for coach review.
