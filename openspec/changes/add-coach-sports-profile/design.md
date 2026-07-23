# Design: Coach sports-profile management

## Ownership check

The endpoint resolves the authenticated user's coach record and compares it with `members.coach_id`. A coach may never access a member assigned to another coach.

## Data lifecycle

`sport_profiles` has a one-to-one relationship with `members`. The update operation will use `updateOrCreate` so a coach can complete a profile even when one has not been created yet.

## AI readiness

The profile stores the exact context required later by the AI generation flow: objective, level, injuries, available days, and training preferences.
