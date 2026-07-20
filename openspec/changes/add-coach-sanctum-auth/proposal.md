# Add secure coach authentication

## Why

Coaches need a secured API workspace before they can manage assigned members and
review AI-generated programmes.

## What changes

- Add a coach-only Sanctum Bearer-token login and logout flow.
- Add a protected coach identity endpoint.
- Reuse the existing role middleware and user resource.
- Add focused Pest coverage for coach authentication and access control.

## Out of scope

- Coach profile creation, member management, programme CRUD, AI generation, and
  any admin or member workflow.
