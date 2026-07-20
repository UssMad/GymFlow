## ADDED Requirements

### Requirement: Coach Bearer-token login
The API MUST authenticate existing coach users using Laravel Sanctum tokens.

#### Scenario: Coach submits valid credentials
- **WHEN** a coach posts valid email and password to the coach login endpoint
- **THEN** the API returns HTTP 200 with a Bearer token and user resource.

#### Scenario: Another role submits valid credentials
- **WHEN** an admin or member posts valid credentials to the coach login endpoint
- **THEN** the API returns HTTP 403 and no token is issued.

### Requirement: Protected coach endpoint
Coach workspace endpoints MUST require Sanctum authentication and the coach role.

#### Scenario: Request has no token
- **WHEN** a client requests the coach identity endpoint without a token
- **THEN** the API returns HTTP 401.

#### Scenario: Authenticated coach requests identity
- **WHEN** an authenticated coach requests the coach identity endpoint
- **THEN** the API returns HTTP 200 and the coach user resource.
