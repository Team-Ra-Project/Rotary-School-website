# Recent Updates — PHP & MySQL Backend

This adds a small, focused backend to the existing Rotary School Uran website so
the **Recent Updates** section (shown on the Home page and the News page) is
managed from a database instead of hard-coded HTML.

**Nothing about the existing site's design was changed.** No HTML layout, CSS,
JavaScript animations, or responsiveness were touched, except for one file:
`assets/js/public-updates.js`, where the mock data array was swapped for a real
`fetch()` call to the new API — the rendering code and markup it produces are
identical to before.

This is intentionally **not** a full CMS. It only manages the Recent Updates /
News feed, exactly as scoped. The other admin links you may see in the old
`admin-dashboard.html` / `admin-updates.html` / `login.html` preview pages are
unrelated Phase-1 mockups from the original frontend build and are not wired
to this backend — the real, working admin area is under `/admin/`.

---

## 1. What was added

```
frontend/
├── admin/                    Admin-only pages (all require login)
│   ├── login.php               Admin login form (only entry point — no register/user login)
│   ├── logout.php
│   ├── dashboard.php            Add-update form + list of all updates (edit/delete/publish/hide)
│   ├── add-update.php           Handles "add" form submission
│   ├── edit-update.php          Edit form + handles "save changes"
│   ├── delete-update.php        Handles delete (POST only, CSRF-checked)
│   └── toggle-status.php        Handles publish/hide toggle (POST only, CSRF-checked)
├── api/
│   └── updates.php              Public read-only JSON endpoint (published items only)
├── config/
│   └── database.php              Edit this with your MySQL credentials
├── includes/
│   ├── auth.php                    Session helpers (require_admin_login, etc.)
│   ├── csrf.php                     CSRF token helpers
│   ├── upload.php                    Secure image upload validation/storage
│   └── functions.php                  Escaping, validation, flash messages
├── database/
│   └── schema.sql                      Creates `admins` + `recent_updates`, seeds 1 admin
├── uploads/
│   └── news/                             Uploaded featured images land here
└── assets/js/public-updates.js         (only file edited) now fetches api/updates.php
```

Everything else in `frontend/` is exactly as it was.

---

## 2. Requirements

- PHP 8.0+ with the `pdo_mysql`, `mbstring`, and `fileinfo` extensions (all
  enabled by default in most hosting/XAMPP/WAMP setups)
- MySQL 5.7+ / MariaDB 10.3+
- A web server that runs PHP (Apache/Nginx, or XAMPP/WAMP/MAMP for local use)

---

## 3. Setup

### Step 1 — Import the database
Create the database and tables by importing `database/schema.sql`:

```bash
mysql -u root -p < database/schema.sql
```

(or open phpMyAdmin → **Import** → choose `database/schema.sql`)

This creates the `rotary_school` database with two tables (`admins`,
`recent_updates`) and inserts the default admin account plus one sample
update so the site isn't empty on first load.

### Step 2 — Configure the database connection
Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rotary_school');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Change these to match your MySQL setup (most hosts give you a specific DB
name/user/password to use).

### Step 3 — Make the uploads folder writable
The web server needs to be able to write new images into `uploads/news/`:

```bash
chmod 755 uploads/news
```

### Step 4 — Deploy
Upload the entire `frontend/` folder to your web server as usual (it's the
same folder that already contains `index.html`, `news.html`, etc. — the
backend lives alongside it, it doesn't replace anything).

### Step 5 — Log in
Visit `/admin/login.php` and log in with the default credentials below.

---

## 4. Default admin credentials

```
URL:      /admin/login.php
Username: admin
Password: Admin@12345
```

**Change this password immediately after your first login.** There is no
"change password" screen in this minimal build (to keep the scope small), so
to set a new password:

1. Generate a new bcrypt hash for your chosen password. The easiest way is a
   one-line PHP script — save this as e.g. `hash.php`, run it once with
   `php hash.php`, then delete it:
   ```php
   <?php
   echo password_hash('YourNewPassword123!', PASSWORD_BCRYPT), PHP_EOL;
   ```
2. Copy the output hash.
3. Update the admin row in MySQL:
   ```sql
   UPDATE admins SET password_hash = 'PASTE_HASH_HERE' WHERE username = 'admin';
   ```

---

## 5. How it works

- **Admin login** (`admin/login.php`) — single admin account, no
  registration, no visitor/user login. Sessions are cookie-based
  (`HttpOnly`, `SameSite=Lax`), and every form is protected by a CSRF token.
- **Dashboard** (`admin/dashboard.php`) — add a new update (title,
  description, date, optional image, published/hidden), and manage existing
  ones: **Edit**, **Publish/Hide**, **Delete**.
- **Image uploads** — validated server-side by actual file content (not just
  the extension) using `finfo`/`getimagesize`, restricted to JPG, JPEG, PNG,
  and WebP, capped at 5 MB, renamed to a random filename, and stored in
  `uploads/news/`. PHP execution is disabled inside `uploads/` via
  `.htaccess` as a defense-in-depth measure.
- **Public feed** (`api/updates.php`) — a read-only JSON endpoint that only
  ever returns rows where `status = 'published'`. Both `index.html` (Home)
  and `news.html` (News) call this same endpoint through
  `assets/js/public-updates.js`, so anything you add, edit, publish, hide, or
  delete in the admin panel is reflected on both pages immediately — no
  manual syncing needed.
- **Security**: passwords are hashed with `password_hash()`/`password_verify()`
  (bcrypt); all database queries use PDO prepared statements; every
  state-changing admin action requires a valid CSRF token; all form input is
  server-side validated; `config/`, `includes/`, and `database/` are blocked
  from direct web access via `.htaccess`.

---

## 6. Notes

- If your host doesn't support `.htaccess` (e.g. Nginx), apply the equivalent
  `deny`/`location` rules for `config/`, `includes/`, `database/`, and disable
  PHP execution inside `uploads/` in your server config instead.
- The `recent_updates` table stores everything this project needs — title,
  description, image path, date, and status — nothing more was added, per the
  "no full CMS" requirement.
