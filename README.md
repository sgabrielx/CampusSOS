# CampusSOS Web Management System (PHP)
Internal console for security personnel and administrators. Requires PHP 8+ with PDO SQLite (included in XAMPP).

## Run
1. Copy this folder to `htdocs/campussos-php` (XAMPP), or run `php -S localhost:8000` inside it.
2. Open `http://localhost:8000/login.php` (or `http://localhost/campussos-php/login.php`).
3. First request creates `data/campussos.sqlite` from `schema.sql` and seeds fictional demo data. Delete that file to reset.

## Demo accounts (development only; change before real use)
admin@demo.campussos (Administrator), reyes@demo.campussos (Security). Password: demo1234

## Implemented
Login with password hashing, sessions and CSRF tokens; role checks on the server; dashboard; SOS and incident lists with search/filters; case detail with validated status transitions, status history and assignment (admin only); cancellation review (original record kept, SOS needs a reason); user management; audit log. All queries use prepared statements.

## Not implemented yet
Spam review, feedback, analytics page, campus map, evidence uploads, notifications, mobile-app API endpoints, login rate limiting beyond a 1-second delay. Not connected to the mobile app.

## Switching to MySQL
Change the PDO DSN in `db()` in `lib.php` and adapt `schema.sql` (AUTOINCREMENT types).
