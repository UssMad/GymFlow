# Change: Add subscription management

## Why

GymFlow needs a payment-free subscription history so administrators can assign membership plans and reliably identify active or expired access.

## What Changes

- Add a `subscription_plans` catalog with duration and descriptive data.
- Add a `member_subscriptions` history with member, plan, start date, end date, and status.
- Add administrator APIs to assign a plan and view a member's subscription history.
- Keep `members.statut_abonnement` aligned with the latest assigned subscription.

## Out of Scope

Online payment, payment providers, invoices, and transaction records are not included.
