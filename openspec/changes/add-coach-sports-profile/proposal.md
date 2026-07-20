# Change: Add coach sports-profile management

## Why

Coaches need accurate member objectives, level, injuries, availability, and preferences before they can create a safe, personalized programme or request AI generation.

## What Changes

- Allow a coach to view the sports profile of a member assigned to them.
- Allow the assigned coach to create or update that profile through a validated API endpoint.
- Keep access scoped to the coach-member assignment.

## Impact

- Affected routes: `/api/coach/members/{member}/sport-profile`
- Affected code: coach controller, validation, resource responses, and feature tests.
