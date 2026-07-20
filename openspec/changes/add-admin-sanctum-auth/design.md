# Design

## Authentication flow

An existing user with role `admin` submits email and password to the admin login
endpoint. After credentials and role are verified, Laravel Sanctum issues a
personal access token with the `admin` ability. The client sends it as a
`Bearer` token for protected requests.

## Route protection

The admin identity and logout endpoints require `auth:sanctum` plus a `role`
middleware alias that only permits the `admin` role. Login deliberately checks
the role itself, so a valid coach or member account cannot obtain an admin
token.

## Response contract

Successful login returns HTTP 200, token type `Bearer`, token, and a
`UserResource`. Invalid credentials return 422 without revealing whether the
email exists. A non-admin account returns 403.
