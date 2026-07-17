## 1. MySQL configuration

- [x] 1.1 Create the local `gymflow` MySQL database.
- [x] 1.2 Configure the ignored `.env` file for the local MySQL connection.
- [x] 1.3 Update safe MySQL defaults in `.env.example`.

## 2. Initial domain schema

- [x] 2.1 Add the GymFlow role to users.
- [x] 2.2 Create migrations for coaches, members, and sport profiles.
- [x] 2.3 Create migrations for AI generations, programmes, workout sessions,
  exercises, and exercise details.
- [x] 2.4 Run migrations and verify the resulting schema.

## Verification

- [x] `php artisan migrate` completed against local MySQL.
- [x] `php artisan migrate:status` reports every migration as ran.
- [x] MySQL table listing contains all GymFlow tables.
- [x] Laravel Pint passed for the changed migrations.
- [ ] Full test suite: timed out in this environment and needs separate follow-up.
