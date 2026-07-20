# Design: Admin member management

## Authorization

Every endpoint is protected by `auth:sanctum` and `role:admin`. The client never submits a member role: the server assigns `member` during account creation.

## Consistency

Creating a member account spans `users` and `members`. A database transaction ensures that neither record remains if the other write fails.

## Response shape

`MemberResource` exposes the member, related account, assigned coach, and sport profile when loaded. Password hashes are never returned.
