# Design: Subscription management

## Data model

`subscription_plans` is an administrative catalog. `member_subscriptions` records every plan assignment for a member, making historical periods queryable without overwriting prior data.

## Status resolution

The effective status is calculated from the stored status and end date. A suspended subscription remains suspended; otherwise, a past end date makes it expired and any current or future end date makes it active.

## Scope

This change manages gym access only. No payment, invoice, or external payment gateway is represented.
