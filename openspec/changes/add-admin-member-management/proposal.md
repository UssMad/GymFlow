# Change: Add admin member management API

## Why

Administrators need a secure way to create and maintain GymFlow member accounts, subscription states, and coach assignments without exposing role changes to clients.

## What Changes

- Add protected REST endpoints for listing, creating, viewing, and updating members.
- Create the linked `users` and `members` records together in a database transaction.
- Force newly created accounts to use the `member` role.
- Validate requests with dedicated Form Requests and return a stable Member API Resource.

## Impact

- Affected API routes: `/api/admin/members`
- Affected code: admin API controller, request validation, resource responses, and feature tests.
