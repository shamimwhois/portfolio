# Deploy to cPanel (shared hosting) — no directory changes needed

This project ships with a root **`.htaccess`** that lets you upload the **whole
project folder into `public_html`** and have it run immediately. Laravel's
`public/` folder stays exactly where it is — **no file moves, no symlinks, no
subdomain, no `.htaccess` editing**.

The root `.htaccess` was verified against Apache 2.4: every route, query
string and static asset works, while `.env`, `vendor/`, `storage/`,
`database/`, `config/` etc. return **403 Forbidden**.

---

## 1. Requirements

| Requirement | Check in cPanel |
| --- | --- |
| **PHP 8.3 or newer** | Software → **Select PHP Version** (or MultiPHP Manager) → pick **PHP 8.3.x** |
| **mod_rewrite** | On by default; `.htaccess` is honored by default on cPanel |
| **SQLite** (recommended) or **MySQL** | See step 4 — SQLite needs *zero* setup |
| **Vendor folder** | Already included if you zip the whole project |

> ⚠️ **Do not use PHP 8.2** — this app requires `php: ^8.3` (composer.json)
> and will not boot on 8.2.

---

## 2. Upload

1. On your computer, **zip the project folder** (everything — `app/`, `public/`,
   `vendor/`, `storage/`, `.env`, `.htaccess`, …). If `.env` is missing from the
   zip, see step 5.
2. cPanel → **File Manager** → open **`public_html`** → **Upload** → select the zip.
3. Right-click the zip → **Extract** so the project's files land **directly
   inside `public_html`** (e.g. `public_html/index.php` should NOT exist — but
   `public_html/public/index.php` should, next to the uploaded `.htaccess`).
4. Delete the zip from the server.

Done — the root `.htaccess` routes everything into `public/` automatically.

---

## 3. Permissions (the #1 cause of 500 errors)

Make these **writable** by the web server (cPanel: select folder → *Permissions* →
`755`, or `775` if it still errors):

```
storage/             (recursive — contains logs, sessions, cache, uploads)
bootstrap/cache/     (recursive)
database/database.sqlite   (the SQLite file itself must be writable)
```

`storage/app/private/feed/**` must also be writable if you post feed media.

---

## 4. Database

### Option A — SQLite (recommended, zero setup) ✅
This app already uses **SQLite** (`DB_CONNECTION=sqlite`), including for
sessions. Just make sure `database/database.sqlite` is uploaded and writable
(step 3). Nothing to create in cPanel.

### Option B — MySQL (cPanel MySQL Databases)
1. Create a database + user in **MySQL Databases** and add the user to the DB (All Privileges).
2. Edit the server `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   ```
3. Run the migrations from **cPanel → Terminal**:
   ```bash
   php artisan migrate --force
   ```

---

## 5. `.env` and app key

- The zip should contain `.env`. Open it in cPanel File Manager and set:
  ```
  APP_URL=https://your-domain.com
  APP_ENV=production
  APP_DEBUG=false
  ```
- If `.env` was **not** uploaded, copy the local one into `public_html/.env`,
  then from **cPanel → Terminal** generate the key:
  ```bash
  php artisan key:generate
  ```

---

## 6. First visit

Open `https://your-domain.com` — the homepage renders. Then:

- **Admin console** → `/admin` — login `admin@scifi-hud.test` / `password`
  (change this immediately — it's a seeded default).
- **Customer panel** → `/panel` — login `client@scifi-hud.test` / `password`.
- Feed media, project downloads and the AI assistant all store to `storage/`
  and are served by the app's own routes — no `storage:link` required.

> The Vite/JS/CSS assets are pre-built in `public/build` — **no `npm install`
> is needed** on the server. Rebuild locally with `npm install && npm run build`
> and re-upload `public/build` if you change front-end code.

---

## 7. Troubleshooting

| Symptom | Fix |
| --- | --- |
| **500 on every page** | PHP version (must be 8.3+); `storage/` + `bootstrap/cache/` not writable (step 3) |
| **Blank page** | Same as above, or `.env` missing / `APP_KEY` empty (step 5) |
| **404 on every page** | Root `.htaccess` missing or not read (step 2 — it must sit in `public_html` next to the project files) |
| **403 on some paths** | That's the security block working — don't remove it |
| **"SQLSTATE" errors** | SQLite file not writable, or MySQL credentials wrong (step 4) |
| **Uploads fail** | `storage/app/private` writable + PHP `upload_max_filesize`/`post_max_size` high enough for video (64 MB) |

## 8. Security notes (already handled)

- `.env`, `.env.example`, `.git`, `app/`, `vendor/`, `storage/`, `database/`,
  `config/`, `bootstrap/`, `tests/`, `resources/`, `routes/`, `docs/` →
  **403** via the root `.htaccess`.
- Directory listings are **disabled**.
- Keep `APP_DEBUG=false` and `APP_ENV=production` in production.
