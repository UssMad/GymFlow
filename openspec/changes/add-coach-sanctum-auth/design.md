# Design

## Authentication flow

An existing user with role `coach` submits credentials to the coach login
endpoint. Valid credentials issue a Sanctum token with the `coach` ability.
Only the `coach` role can log in through this endpoint.

## Route protection

The coach identity and logout endpoints use `auth:sanctum` and `role:coach`.
This keeps a valid admin or member token outside the coach workspace.

## Reuse

The feature reuses Sanctum, the `EnsureUserHasRole` middleware, and
`UserResource` introduced by GFRS-11, avoiding a second authentication system.
