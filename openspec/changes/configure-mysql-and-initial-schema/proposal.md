# Configure MySQL and the initial GymFlow schema

## Why

GymFlow needs a reproducible MySQL setup and a domain schema that supports
members, coaches, sport profiles, coach-validated programmes, exercises, and
AI generation history.

## What changes

- Document MySQL as the default local connection in `.env.example`.
- Create the initial GymFlow migrations from the approved MCD/MLD.
- Add referential integrity and indexes for the expected access patterns.

## Out of scope

- Payments and checkout.
- Subscription and attendance workflows.
- API endpoints, authentication behaviour, Blade pages, AI calls, queue jobs,
  and tests.
