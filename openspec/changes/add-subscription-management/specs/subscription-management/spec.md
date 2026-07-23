## ADDED Requirements

### Requirement: Subscription plans and member history are stored
The system SHALL store a subscription-plan catalog and a history of plan assignments for each member.

#### Scenario: Record an assignment
- **WHEN** an administrator assigns a plan to a member
- **THEN** the system stores the plan, start date, end date, and status in the member's subscription history.

### Requirement: Subscription status is resolved from dates
The system SHALL expose active, expired, or suspended subscription status based on each recorded subscription.

#### Scenario: Expired subscription
- **WHEN** a non-suspended subscription has an end date before today
- **THEN** the system exposes its status as `expire`.

### Requirement: Administrators can assign subscriptions
The system SHALL allow authenticated administrators to assign a valid subscription plan to a member and view that member's subscription history.

#### Scenario: Invalid assignment dates
- **WHEN** an administrator submits an end date before its start date
- **THEN** the system rejects the request with HTTP 422.
