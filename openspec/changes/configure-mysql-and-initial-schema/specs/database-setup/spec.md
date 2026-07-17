## ADDED Requirements

### Requirement: MySQL local configuration
The project MUST document a MySQL local connection in `.env.example` using the
`gymflow` database and UTF-8 MB4 encoding.

#### Scenario: Developer configures a new local checkout
- **WHEN** a developer copies `.env.example` to `.env`
- **THEN** Laravel is configured to use the MySQL connection and `gymflow`
  database by default.

### Requirement: Initial GymFlow domain schema
The project MUST provide reversible migrations for users, coaches, members,
sport profiles, AI generations, programmes, workout sessions, exercises, and
exercise details.

#### Scenario: Developer migrates an empty database
- **WHEN** `php artisan migrate` runs against an empty MySQL `gymflow` database
- **THEN** all required tables, foreign keys, unique constraints, and indexes
  are created successfully.

### Requirement: Coach validation boundary
The schema MUST keep an AI generation separate from its resulting programme and
allow a programme to reference its validating coach.

#### Scenario: Coach validates an AI-generated programme
- **WHEN** a generated programme is persisted
- **THEN** it may reference one generation and one validating coach without
  making publication automatic.
