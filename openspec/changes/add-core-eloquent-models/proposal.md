# Add core Eloquent models and relationships

## Why

The MySQL schema is in place, but GymFlow needs Eloquent models to express the
approved MCD/MLD inside Laravel before authentication and feature endpoints are
implemented.

## What changes

- Add the domain models for the GymFlow schema.
- Define explicit Eloquent relationships, fillable fields, casts, and schema
  defaults.
- Keep model names independent from Laravel's internal `sessions` table by using
  `WorkoutSession` for `workout_sessions`.

## Out of scope

- Controllers, routes, Form Requests, API Resources, authentication, policies,
  AI integration, queues, Blade views, and CRUD workflows.
