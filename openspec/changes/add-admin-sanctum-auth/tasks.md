## 1. API and Sanctum setup

- [x] 1.1 Install Laravel API support and Sanctum.
- [x] 1.2 Configure API route loading and the User token trait.

## 2. Admin authentication

- [x] 2.1 Add login validation, controller actions, and user resource.
- [x] 2.2 Add role middleware and protected admin routes.

## 3. Verification

- [x] 3.1 Add Pest coverage for successful login, 403 role denial, and 401
  protected access.
- [x] 3.2 Run formatter and the focused feature test.

## Runtime note

- [ ] Apply the Sanctum token migration to local MySQL. This is blocked by the
  current XAMPP MySQL TCP authentication-plugin configuration; the focused Pest
  suite uses SQLite and passes.
