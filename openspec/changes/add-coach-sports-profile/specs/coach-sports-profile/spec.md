## ADDED Requirements

### Requirement: Coaches can view assigned member sports profiles
The system SHALL allow an authenticated coach to retrieve the sports profile of a member assigned to that coach.

#### Scenario: Assigned member profile
- **WHEN** a coach requests the sports profile of one of their assigned members
- **THEN** the system responds with HTTP 200 and the member's objective, level, injuries, available days, and preferences.

#### Scenario: Unassigned member profile
- **WHEN** a coach requests the profile of a member assigned to another coach
- **THEN** the system responds with HTTP 403.

### Requirement: Coaches can maintain assigned member sports profiles
The system SHALL allow an authenticated coach to create or update the sports profile for a member assigned to that coach.

#### Scenario: Update sports profile
- **WHEN** a coach submits valid sports-profile data for an assigned member
- **THEN** the system persists the profile data for later AI programme generation
- **AND** responds with the updated profile.
