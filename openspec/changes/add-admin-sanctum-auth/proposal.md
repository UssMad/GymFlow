# Add secure admin authentication

## Why

Administrators need a secure way to access GymFlow management endpoints. The
application requires token-based REST API authentication before admin features
can be implemented.

## What changes

- Install and configure Laravel Sanctum for Bearer tokens.
- Add an admin login and logout API flow.
- Protect an admin identity endpoint with Sanctum and role middleware.
- Return authenticated user data through an API Resource.
- Add focused Pest coverage for login and access control.

## Out of scope

- Public registration, password reset, email verification, coach login, member
  login, and all management CRUD endpoints.
