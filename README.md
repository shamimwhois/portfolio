<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/version-1.0.0-2af6ff?style=for-the-badge&labelColor=05070f" alt="Version"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-ff3a98?style=for-the-badge&labelColor=05070f" alt="Laravel 13"></a>
  <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-4.x-9657ff?style=for-the-badge&labelColor=05070f" alt="Livewire 4"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-%5E8.3-4ef8c5?style=for-the-badge&labelColor=05070f" alt="PHP 8.3"></a>
  <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-4.x-2af6ff?style=for-the-badge&labelColor=05070f" alt="Tailwind CSS 4"></a>
</p>

# Sci-Fi HUD Portfolio

> A mission-control themed portfolio platform — full-stack, database-driven, and database-managed.

The **Sci-Fi HUD Portfolio** transforms a static "mission control" HUD portfolio into a complete
web application built on **Laravel 13 + Livewire 4**. It preserves the iconic sci-fi visual language
— Orbitron/Rajdhani typography, neon cyan/magenta/violet palette, matrix background, glitch and
typewriter effects, HUD frames and a 10-theme switcher — while adding real application logic:

- **Public portfolio** — hero, about, skills matrix, projects, services, tech packages, timeline, FAQ, blog and contact, all editable from a database.
- **Customer portal** (`/panel`) — dashboard, invoices, purchased services, support chat, profile.
- **Admin console** (`/admin`) — full content management, users, orders, messages, settings, AI and payment configuration.
- **Checkout** — manual channels (bKash, Nagad, Binance P2P, PayPal) plus real gateway processing (Stripe, Binance Pay B2B, bKash, Razorpay, SSLCommerz, AmarPay) with signed webhooks and pending-invoice lifecycle.
- **AI support assistant** — floating HUD widget backed by any OpenAI-compatible endpoint, with an offline keyword fallback.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Documentation](#documentation)
- [Quickstart](#quickstart)
- [Demo Credentials](#demo-credentials)
- [Roles & Permissions](#roles--permissions)
- [Routes](#routes)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Security](#security)
- [License](#license)

## Features

### Public Site
- **Home** — glitch hero title, typewriter subtitle, electric-frame portrait, animated stats, and all sections below.
- **Package & Tech Command Center** — tabbed grids (JavaScript / PHP / Python / Package Managers) with package detail modals, install commands, docs and repo links; priced packages are purchasable.
- **Skills Matrix** — animated skill bars.
- **Project Previews** — card grid with a live-preview modal.
- **Services** — category tabs (Web Design / Web Dev / Marketing / SEO) rendering pricing tiers with a full checkout step.
- **Project Timeline** — alternating phase cards.
- **FAQ Protocol** — accordion.
- **Blog** — search + tag filtering, published posts only.
- **Contact** — form storing submissions into an admin inbox.
- **Support AI assistant** — floating chat widget (guest or customer).

### Authentication
- Register (with live validation), login (rate-limited), logout.
- Guests are deep-linked back into checkout after signup (`?plan=` / `?pkg=`).

### Customer Panel (`/panel`)
- Dashboard with stats, recent invoices and latest blog posts.
- Invoices with status filters (all / pending / paid / cancelled).
- Purchased services & package licenses with install commands.
- Support conversations with the site owner.
- Profile & password management.

### Admin Console (`/admin`)
- Dashboard with revenue, orders, unread messages and an activity feed.
- Full CRUD for: users, projects, skills, services (categories + tiers), packages, blog, FAQ, timeline.
- Order management — mark paid / cancel / refund.
- Messages inbox + support conversation management.
- Settings — Site, SEO, Appearance (themes + section visibility), AI Assistant, Payments.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13, PHP ^8.3 |
| Reactive UI | Livewire 4 |
| Templates | Blade components + partials |
| Styling | Tailwind CSS (CDN for Livewire layouts; Tailwind 4 + Vite for compiled entry points) |
| Database | SQLite (configurable via `.env`), Eloquent ORM |
| Assets | Vite, `public/assets/` static CSS/JS |
| Testing | PHPUnit feature tests |
| Tooling | Pint (code style), Pail (logs), Tinker |

## Documentation

| Document | Description |
| --- | --- |
| [`docs/PRD.md`](docs/PRD.md) | Product requirements — personas, functional requirements, data model, scope. |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Full project analysis — structure, routing, models, Livewire components, key flows. |
| [`docs/DESIGN.md`](docs/DESIGN.md) | Design system reference — tokens, themes, components, motion, accessibility. |
| [`SECURITY_AUDIT.md`](SECURITY_AUDIT.md) | Static security audit with prioritized remediation list. |
| [`DEPLOY_CPANEL.md`](DEPLOY_CPANEL.md) | Deployment guide for cPanel / shared hosting. |
| [`CHANGELOG.md`](CHANGELOG.md) | Release history and notable changes. |

## Quickstart

Requires PHP ^8.3, Composer and Node.js (npm).

```bash
# 1. Install dependencies, create .env, generate key, migrate & build assets
composer setup

# 2. Seed demo content (users, settings, skills, projects, services, packages,
#    blog, FAQs, timeline, invoices, support conversations)
php artisan db:seed --force

# 3. Serve the application + queue worker + Vite (development)
composer dev
```

> `composer dev` runs `php artisan serve`, the queue worker (`queue:listen`), `php artisan pail`
> (logs) and Vite concurrently. For a simple single-server run: `php artisan serve`.

### Environment notes (`.env`)

| Variable | Purpose |
| --- | --- |
| `APP_NAME` | Application name shown in metadata / tests (use `Sci-Fi HUD Portfolio`). |
| `DB_CONNECTION` | `sqlite` by default — the database file lives at `database/database.sqlite`. |
| `USER_LLM_API_KEY` | Optional. Leave empty to run the AI assistant in offline fallback mode. |
| `USER_LLM_BASE_URL` / `USER_LLM_MODEL` | OpenAI-compatible Chat Completions endpoint + model. |
| `TRUSTED_PROXIES` | Comma-separated proxy IPs. Leave unset for local; set real proxy IPs in production (see Security Audit C2). |
| `ADMIN_SEED_PASSWORD` | Optional. Overrides the seeded admin password instead of the default `password`. |

Payment account numbers in `config/payments.php` are placeholders — replace them with real
accounts via the **Admin → Settings → Payments** screen (or `config/payments.php`) before launch.
Gateway credentials (Stripe, Binance Pay B2B, …) are entered per mode (sandbox/live) on the same
screen — gateways only appear at checkout when enabled and fully configured. Binance Pay B2B
credentials (merchant ID, API key, secret) come from the Binance Pay merchant portal
(https://developers.binance.com/docs/binance-pay); the Binance P2P manual method just needs the
merchant's Binance UID for USDT P2P transfers.

### Feed media (uploads)

The community feed accepts up to 6 images (10 MB each), one video (64 MB) and up to 3 documents
(PDF / Office / txt / csv / zip, 20 MB each). Uploads are validated by extension **and** sniffed
file content; documents are served sandboxed and only when their post is published.

PHP itself must allow the larger uploads. `public/.htaccess` raises `upload_max_filesize` to 64M /
`post_max_size` to 70M for Apache / LiteSpeed / cPanel deployments. For local development (Herd,
Laragon or `php artisan serve`) edit your php.ini and restart:

```ini
upload_max_filesize = 64M
post_max_size = 70M
```

Livewire's temporary-upload cap is already raised to 64 MB in `config/livewire.php`.

## Demo Credentials

After `php artisan db:seed --force`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@scifi-hud.test` | `password` |
| Admin (ops) | `ops@scifi-hud.test` | `password` |
| Customer | `client@scifi-hud.test` | `password` |
| Customer | `alex@scifi-hud.test` | `password` |
| Customer (deactivated) | `maria@scifi-hud.test` | `password` |

## Roles & Permissions

| Role | Access |
| --- | --- |
| Guest | Public site, blog, contact form, HUD assistant, register / login |
| Customer (`role = customer`) | Public site + customer panel (`/panel`) |
| Admin (`role = admin`) | Everything above + admin console (`/admin`) |

Access is enforced by the `auth`, `admin` and `EnsureUserIsActive` middleware. Deactivated users are
logged out and redirected to login on any authenticated route.

## Routes

### Public
| Method | URI | Name |
| --- | --- | --- |
| GET | `/` | `home` |
| GET | `/blog` | `blog.index` |
| GET | `/blog/{post:slug}` | `blog.show` |
| GET | `/contact` | `contact` |

### Guest
| Method | URI | Name |
| --- | --- | --- |
| GET | `/login` | `login` |
| GET | `/register` | `register` |
| POST | `/logout` | `logout` |

### Customer Panel (`auth` + active)
| URI | Name |
| --- | --- |
| `/panel` | `panel.dashboard` |
| `/panel/invoices` | `panel.invoices` |
| `/panel/services` | `panel.services` |
| `/panel/support` | `panel.support` |
| `/panel/profile` | `panel.profile` |

### Admin Console (`auth` + admin + active)
| URI | Name |
| --- | --- |
| `/admin` | `admin.dashboard` |
| `/admin/users` · `/admin/projects` · `/admin/skills` · `/admin/services` · `/admin/packages` · `/admin/blog` · `/admin/faqs` · `/admin/invoices` · `/admin/timeline` · `/admin/messages` · `/admin/conversations` | Content management |
| `/admin/settings` → redirects to Site | `admin.settings` |
| `/admin/settings/site` · `/admin/settings/seo` · `/admin/settings/appearance` · `/admin/settings/ai` · `/admin/settings/payments` | Settings |

## Project Structure

```
app/
├── Livewire/              # All interactive components
│   ├── Admin/             # Console CRUD + settings (Site, SEO, Appearance, AI, Payments)
│   ├── Auth/              # Login, Register
│   ├── Blog/              # Index, Show
│   ├── Home/              # Index (home sections)
│   ├── Panel/             # Dashboard, Invoices, Services, Support, Profile
│   ├── Sections/          # Packages, Services (public checkout widgets)
│   └── Support/           # Assistant (floating HUD widget)
├── Models/                # Eloquent models for all 14 tables
├── Services/Ai/           # OpenAI-compatible Chat Completions client
├── Support/               # Payments, Nav, AiConfig, SupportAssistant
└── Http/Middleware/       # AdminAccess, EnsureUserIsActive
config/                    # payments, llm, nav + Laravel defaults
database/
├── migrations/            # 12 migrations (schema + data-safe updates)
├── factories/             # UserFactory
└── seeders/               # DatabaseSeeder (demo content)
public/assets/
├── css/                   # hud.css, panel.css, admin-enhanced.css, auth.css
├── js/                    # hud.js (motion), hud-chrome.js (themes + audio)
└── image/                 # pic.jpg (portrait)
resources/views/           # Blade layouts, components, Livewire views
routes/web.php             # All web routes
tests/Feature/             # 10 feature test classes
```

## Testing

```bash
# Full suite (unit + feature) — 10 feature classes, ~100 assertions
composer test

# Code style
php artisan pint
```

The suite covers: public page rendering, authentication & access control, blog visibility,
checkout (services + packages), payment methods, admin CRUD, settings & invoice management,
the AI assistant and provider configuration. Tests run on an in-memory SQLite database
(`phpunit.xml`).

## Security

- Laravel authentication with a rate-limited login (5 attempts / minute).
- Admin routes behind `auth` + `admin` + active-user middleware; customer panel behind `auth`.
- Eloquent exclusively (parameterized queries); Blade escapes all output except the intentionally
  rendered blog body.
- Passwords hashed; `.env` is git-ignored (no secrets in the repo).
- See [`SECURITY_AUDIT.md`](SECURITY_AUDIT.md) for a full audit and prioritized remediation list
  (trusted proxies, HTML sanitization, rate limiting, security headers, and more).

## License

Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
