## ADDED Requirements

### Requirement: Admins can create a member account
The system SHALL allow an authenticated administrator to create a member account and its associated member record in one request.

#### Scenario: Valid member creation
- **WHEN** an authenticated administrator submits valid personal, credential, and optional coach data
- **THEN** the system creates a `users` record with role `member`
- **AND** creates its linked `members` record
- **AND** responds with HTTP 201 and the new member resource.

### Requirement: Member management is admin-only
The system SHALL protect member listing, viewing, creation, and updates with Sanctum authentication and the admin role.

#### Scenario: Non-admin access
- **WHEN** an unauthenticated or non-admin user calls an admin member endpoint
- **THEN** the system responds with HTTP 401 or HTTP 403 respectively.

### Requirement: Admins can maintain a member safely
The system SHALL allow administrators to list, view, and update a member's account details, subscription status, and coach assignment.

#### Scenario: Update a member
- **WHEN** an authenticated administrator sends valid member changes
- **THEN** the system updates the related account and member record
- **AND** does not allow the API request to change the account role.
