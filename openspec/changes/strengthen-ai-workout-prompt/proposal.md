# Strengthen AI workout prompt and output contract

## Why

The workout generator must consistently use the full sports profile and coach constraints while making safe, reviewable draft recommendations.

## What changes

- Replace the generic JSON prompt with an explicit safety-focused template.
- Pass coach specialty and availability with the member context.
- Document and test the structured weekly-programme output contract.
