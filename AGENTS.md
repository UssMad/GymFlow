# GymFlow Project Instructions

## Project
GymFlow is a gym management application built with Laravel and MySQL.
Its main value is creating personalized weekly workout programmes with AI.

## Roles
- Admin: manages users, coaches, members, exercises, and subscriptions.
- Coach: manages assigned members, requests AI programme generation, reviews, edits, validates, and publishes programmes.
- Member: completes sport profile, views programmes, and tracks each session.

## Core Business Rule
AI never publishes a programme automatically.
The AI creates a draft. A coach must review, edit if needed, validate, then publish it.

## Key Flow
Member sport profile -> AI generation request -> queue job/worker ->
AI proposal -> draft programme -> coach validation -> publication ->
member session tracking.

## Data Rules
- Keep User, Member, Coach, and SportProfile separate.
- A member has zero or one sport profile.
- A member has zero or one assigned coach.
- GenerationIA is an audit record, not the programme itself.
- A manual programme may exist without an AI generation.
- Use `workout_sessions` for training sessions; do not use Laravel's reserved `sessions` table.
- Session statuses: planifie, realise, non_realise, reporte.

## Technical Stack
- PHP 8.3, Laravel, MySQL
- REST API, Sanctum Bearer tokens
- Form Requests, API Resources, correct HTTP status codes
- Blade only for the demonstration frontend
- JavaScript only for light/dark mode
- Laravel Jobs and Queues for slow AI processing
- Docker, GitHub Actions, deployment later

## Development Workflow
- One feature = one Git branch.
- Before coding, create an OpenSpec proposal.
- Do not implement unrelated changes.
- Use migrations for schema changes.
- Run formatter and tests before committing.