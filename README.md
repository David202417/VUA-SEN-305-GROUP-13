# Student Portal login and registration

This is a small PHP 8 application with student registration, secure login,
session-based authentication, logout, and a protected student dashboard.

## Run locally

From this directory, start PHP's development server:

```bash
php -S 127.0.0.1:8000
```

Then open <http://127.0.0.1:8000>.

The SQLite database is created automatically at `data/students.sqlite` after
the first registration or login request. Passwords are stored with PHP's
`password_hash()` function, and all database writes use prepared statements.

## Main files

- `biodata.html` — registration form
- `Login.html` — login form
- `register.php` — registration validation and persistence
- `login.php` — password verification and session creation
- `dashboard.php` — protected account page
- `logout.php` — session logout
- `connect.php` — session and SQLite connection helpers

