## ADDED Requirements

### Requirement: Admin Bearer-token login
The API MUST authenticate existing admin users with Laravel Sanctum tokens.

#### Scenario: Admin submits valid credentials
- **WHEN** an admin posts valid email and password to the login endpoint
- **THEN** the API returns HTTP 200 with a Bearer token and authenticated user
  resource.

#### Scenario: Non-admin submits valid credentials
- **WHEN** a coach or member posts valid credentials to the admin login endpoint
- **THEN** the API returns HTTP 403 and does not issue a token.

### Requirement: Protected admin endpoint
Admin endpoints MUST require both Sanctum authentication and the admin role.

#### Scenario: Request has no Bearer token
- **WHEN** a client requests the admin identity endpoint without a token
- **THEN** the API returns HTTP 401.

#### Scenario: Authenticated admin requests identity
- **WHEN** an authenticated admin requests the admin identity endpoint
- **THEN** the API returns HTTP 200 and the admin user resource.
