# 🎟️ TicketApp — PHP + Twig Frontend

A simple, clean, and responsive ticket management application built with PHP and Twig. This is the Twig/PHP implementation of the TicketApp used for a three-project assignment (the other implementations use React and Vue).

The app provides a minimal session-based authentication flow and CRUD for support tickets. All data is stored locally in JSON files under `data/` so you can run and test the project without a database.

---

## 🚀 Features

- Session-based Authentication (signup, login, logout)
- Ticket CRUD: create, view, edit, delete
- Dashboard with ticket statistics (total / open / in progress / closed)
- Responsive UI styled with vanilla CSS (files under `public/css`)
- Local JSON storage (no DB required) — `data/users.json` and `data/tickets.json`
- Server-friendly router (`router.php`) with static asset passthrough for `public/`

## 🚀 Live Demo

[View Live Project](https://ticketapp-twig-production.up.railway.app/)

## 💻 Repository

[GitHub Repo](https://github.com/h3ktorr/ticketapp-twig)

## 🧱 Tech Stack

- Language: PHP 8+ (tested with PHP 8.x)
- Templating: Twig
- Autoloading: Composer (PSR-4)
- Storage: Local JSON files in `data/`

## 📂 Project Structure

Root highlights (important files/folders):

- `composer.json` — project dependencies + PSR-4 autoloading
- `router.php` — main router/front controller (handles routing and uses Twig)
- `public/` — static assets served by the web server (css, images, etc.)
  - `public/css/style.css` — main stylesheet
- `src/controllers/` — PHP controllers with app logic
  - `AuthController.php` — signup/login/logout, session handling (uses `data/users.json`)
  - `TicketController.php` — ticket CRUD + stats (uses `data/tickets.json`)
- `templates/` — Twig templates
  - `templates/pages/` — page templates (home, dashboard, auth, tickets, 404)
  - `templates/partials/` — shared UI snippets
- `data/` — JSON files used as simple storage (users.json, tickets.json)
- `vendor/` — Composer packages (Twig is included here after `composer install`)

A typical tree (trimmed):

```
composer.json
router.php
public/
  ├─ css/
  │  └─ style.css
  └─ assets/
src/
  └─ controllers/
      ├─ AuthController.php
      └─ TicketController.php
templates/
  ├─ pages/
  └─ partials/
data/
  ├─ users.json
  └─ tickets.json
vendor/
```

## ⚙️ Installation & Setup

1. Clone the repository:

```powershell
git clone <repo-url> ticketapp-twig
cd ticketapp-twig
```

2. Install PHP dependencies with Composer (make sure `composer` is installed):

```powershell
composer install
```

3. Ensure the `data/` folder is writable by PHP and contains (or can be created with) `users.json` and `tickets.json`. The controllers will create these files automatically if missing.

4. Start the PHP built-in development server from the project root so it serves files from `public/` and uses `router.php` as router:

```powershell
php -S localhost:8000 -t public router.php
```

5. Open your browser at: http://localhost:8000

Notes:

- The `router.php` file allows the built-in server to serve existing static files from `public/` (for example `/css/style.css`) and routes other requests to Twig templates.
- If you prefer, you can point the server directly at `public/` and then include/require `../router.php` from `public/index.php` — `php -S localhost:8000 -t public` — but the recommended command above uses `router.php` directly.

## 🔗 Routes (what's available)

The app uses simple routes handled in `router.php`. Important routes include:

- `GET /` — home/landing page
- `GET|POST /auth/signup` — signup page / form submit
- `GET|POST /auth/login` — login page / form submit
- `GET /auth/logout` — logout and clear session
- `GET /dashboard` — protected dashboard (requires session)
- `GET /tickets` — list tickets (protected)
- `GET|POST /tickets/{id}` — create (`id=new`) or edit ticket (protected)

Controllers and data storage:

- `AuthController` uses `data/users.json`. On signup it adds a user and begins a session under `$_SESSION['ticketapp_session']`.
- `TicketController` uses `data/tickets.json` for CRUD and stats.

## 🔒 Authentication & Session

- Session is stored server-side (PHP session) with a session wrapper saved at `$_SESSION['ticketapp_session']`.
- Login/signup are mocked (passwords stored as plain text in `data/users.json`) — this is intentional for a small local demo but not safe for production.

## 💾 Data & Reset

- Tickets and users are saved as JSON in `data/tickets.json` and `data/users.json` respectively.
- To reset the app data, stop the server and remove or edit those files. The controllers will recreate empty JSON files when needed.

## 🧪 Testing & Manual Checks

- Manually test the site by visiting the routes above and using the UI to create accounts and tickets.
- To verify CSS is served, open: `http://localhost:8000/css/style.css` — you should see the contents of `public/css/style.css`.

## Security & Production Notes

- This project is a learning/demo app. Passwords are stored in plain text and sessions are simplistic. Do NOT use this code as-is in production.
- Recommended production changes:
  - Use a real database (MySQL, PostgreSQL, SQLite).
  - Hash passwords using password_hash()/password_verify().
  - Add CSRF protection to forms.
  - Add proper input validation and error handling.
  - Configure Twig cache and enable proper error handling.

## Contribution & Credits

Built by Kelvin Achi as part of a three-implementation assignment (React, Vue, PHP/Twig).

If you'd like help wiring this to a real database or adding API endpoints, open an issue or contact the author at the email in `composer.json`.

---

Happy hacking — enjoy the TicketApp! 🎫
