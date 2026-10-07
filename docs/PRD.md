# PRD — Sci-Fi HUD Portfolio Platform

**Project:** Sci-Fi HUD Portfolio
**Stack:** Laravel 13 · Livewire 4 · Blade · SQLite · Tailwind CSS (CDN) + Tailwind 4/Vite · Sci-Fi HUD design system
**Source reference:** https://github.com/shamimwhois/sci-fi (static HUD portfolio)
**Version:** 1.1
**Date:** 2026-08-06
**Status:** Implemented — see [ARCHITECTURE.md](ARCHITECTURE.md) and [DESIGN.md](DESIGN.md)

---

## 1. Overview

The Sci-Fi HUD Portfolio Platform transforms the original static HUD portfolio into a full-stack,
database-driven web application. It preserves the iconic "mission control" visual language —
Orbitron/Rajdhani typography, cyan/magenta/violet neon palette, matrix background, glitch and
typewriter effects, HUD frames, and a 10-theme switcher — while adding real application logic:

- Dynamic, content-managed portfolio sections (skills, projects, packages, services, timeline, FAQ, blog).
- A customer-facing authentication system and client portal.
- A full admin console to manage every piece of content, orders, and configuration.
- A settings-driven platform: site identity, SEO, appearance (themes + section visibility), payments, and the AI assistant are all configured from the database.

## 2. Goals

- Rebuild the static portfolio as a maintainable Laravel + Livewire application.
- Move all content out of JavaScript and into a database with an admin editing surface.
- Ship a functional customer portal (invoices, services, support) and admin console.
- Ship a checkout lifecycle (manual channels: bKash, Nagad, Binance P2P, PayPal; real gateways: Stripe, Binance Pay B2B, bKash, Razorpay, SSLCommerz, AmarPay) with pending invoices and admin order management.
- Ship an AI-powered support assistant with an offline fallback.
- Keep the HUD aesthetic pixel-faithful to the original site.

## 3. Non-Goals

- Real payment gateway processing (manual channels only — payments are confirmed by the owner and recorded as a mock invoice lifecycle; the `gateway` config is infrastructure only).
- Multi-language / i18n support.
- Production email delivery (mail logs to the `log` driver).
- Email verification and password reset flows (tracked as future work in [SECURITY_AUDIT.md](../SECURITY_AUDIT.md) — H4/M5).

## 4. Personas & Roles

| Role | Description | Permissions |
| --- | --- | --- |
| Guest | Anonymous visitor | View public portfolio, blog, read FAQs; submit contact form; use the HUD assistant |
| Customer | Registered user (`role = customer`) | Access client portal: dashboard, invoices, services, support, profile |
| Admin | Site owner (`role = admin`) | Everything the customer can do plus the full admin console |

Access enforcement: `auth` + `EnsureUserIsActive` middleware on the customer panel; `auth` + `admin`
+ `EnsureUserIsActive` on the admin console. Deactivated accounts are logged out and redirected to login.

## 5. Functional Requirements

### 5.1 Public Site

- **Home (`/`)** — Livewire page, section visibility controlled by the `sections` setting. Sections:
  - Hero: glitch title, typewriter subtitle, portrait with electric frame, CTA buttons.
  - About: mission profile text + animated stat grid (configurable via Settings).
  - Packages / Tech Command Center: tabbed grid (JavaScript / PHP / Python / Package Managers)
    driven by `tech_packages`; package detail modal with install commands, docs and repo links.
    Priced packages show a "Buy license" action that creates a pending invoice; free packages are
    reference-only. Purchased packages surface in the customer panel with their install commands.
    Guests are routed to registration (`?pkg=`) and continue after signup.
  - Skills Matrix: animated skill bars from `skills`.
  - Project Previews: card grid from `projects`; "Live Preview" modal.
  - Services: category tabs (Web Design / Web Dev / Marketing / SEO) rendering pricing tiers from
    `service_categories` + `service_tiers`; "Select plan" opens a checkout step where the customer
    picks a payment method (bKash, Nagad, Binance P2P, PayPal, or an enabled gateway) and confirms,
    creating a pending invoice. Manual methods keep the manual-confirmation lifecycle; gateways
    redirect to the provider's hosted checkout and confirm via signed webhooks/callbacks. Guests are
    routed to registration (`?plan=`) and continue after signup.
  - Project Timeline: alternating phase cards from `timeline_phases`.
  - FAQ Protocol: accordion from `faqs`.
  - Support AI assistant panel (client-side widget, LLM-backed or keyword fallback).
  - Contact footer + quick access links (navigation driven by `config/nav.php`).
- **Blog (`/blog`)** — Livewire list with search + tag filtering; published posts only.
- **Blog detail (`/blog/{slug}`)** — full post view; unpublished posts return 404.
- **Contact form** — Livewire component, stores into `contact_messages` and shows a success state.

### 5.2 Authentication

- **Register (`/register`)** — Livewire form: name, email, password, confirm; validates, creates a
  `customer` user, logs in, redirects to panel (or back to the originating checkout section).
- **Login (`/login`)** — Livewire form; throttled to 5 attempts/minute per email+IP; redirects
  admin → `/admin`, customer → `/panel`.
- **Logout** — available from panel and admin layouts (POST form).

### 5.3 Customer Panel (`/panel`)

Middleware: `auth` + active. Sections (Livewire pages):
- **Dashboard** — stats: invoice count, paid amount, active services, support tickets; recent invoices; latest blog.
- **Invoices** — table with status filter (all / pending / paid / cancelled incl. refunded) and per-status counts; each row shows the payment method.
- **Services** — purchased service tiers and package licenses (from paid invoices) with billing details and payment method; package install commands are shown.
- **Support** — conversation-based chat with the site owner (create, open, send, close); persisted to `support_conversations` / `support_messages`.
- **Profile** — update name/phone/bio and change password.

### 5.4 Admin Console (`/admin`)

Middleware: `auth` + `admin` + active. Sections (Livewire pages):
- **Dashboard** — totals (users, projects, posts, messages, revenue, pending orders), unread messages, pending orders (customer, item, amount, payment method), recent activity feed.
- **Users** — list, search, create, edit, delete, toggle active, change role (self-protection guards).
- **Projects** — full CRUD (title, description, link, thumbnail, tech tags, status, featured, sort).
- **Skills** — full CRUD (name, level, icon, sort).
- **Services** — manage categories and their pricing tiers (CRUD for both).
- **Packages** — manage tech command-center packages (CRUD; priced vs. reference).
- **Blog** — manage posts (title, slug, excerpt, body, tags, featured, published, published_at).
- **FAQ** — manage questions/answers.
- **Timeline** — manage project phases.
- **Invoices** — order management with status filters; mark paid / cancel / refund with state guards.
- **Messages** — inbox of contact submissions; mark read/unread; delete.
- **Conversations** — support conversation management; reply (reopens), close, reopen.
- **Settings**:
  - **Site** — identity, hero copy, about text, typewriter lines, stats, contact info, social links.
  - **SEO** — meta title/description, OG image/type.
  - **Appearance** — default theme (10 themes), theme switcher toggle, per-section visibility.
  - **AI Assistant** — enable/disable, name, welcome message, system prompt, provider presets (OpenAI, DeepSeek, OpenRouter, Mistral, Ollama, LM Studio, custom), test connection.
  - **Payments** — currency, gateway provider/mode/credentials, per-method enable/disable.

### 5.5 Checkout & Payment Lifecycle

- Both service tiers and tech packages funnel into a **pending invoice** (`status = pending`).
- Payment methods come from `App\Support\Payments` (config `config/payments.php`, overridable via
  the `payments` setting): manual channels (bKash, Nagad, Binance P2P, PayPal) plus any enabled and
  configured gateway (Stripe, Binance Pay B2B, bKash, Razorpay, SSLCommerz, AmarPay). Gateways
  redirect to a hosted checkout and mark invoices paid via verified webhooks/callbacks; manual
  methods are confirmed by the owner.
- Duplicate active orders per item are blocked; guests are deep-linked into registration.
- Admins confirm payments manually: `markPaid` / `cancel` / `refund`.
- Invoice numbers are generated as `INV-YYYY-####`.

### 5.6 AI Support Assistant

- Floating HUD widget on the public site (`livewire:support.assistant`), gated by the `ai_enabled` setting.
- Guests chat under a session-scoped conversation; customers chat under their account.
- Backed by any OpenAI-compatible Chat Completions endpoint (`app/Services/Ai/Client`) with a
  knowledge system prompt (services, pricing, packages, FAQ) built by `App\Support\SupportAssistant`.
- Falls back to offline keyword replies when no API key is configured or the provider errors.
- Quick-action chips (pricing / delivery / packages / order status).

## 6. Data Model

All tables below match the shipped migrations. `users` no longer carries a `username` column.

| Table | Purpose |
| --- | --- |
| `users` | `name`, `email`, `role` (admin/customer), `avatar`, `phone`, `bio`, `is_active` |
| `password_reset_tokens`, `sessions` | Laravel auth scaffolding |
| `skills` | `name`, `level`, `icon`, `sort_order` |
| `projects` | `title`, `description`, `link`, `thumbnail`, `tech` (json), `status`, `featured`, `sort_order` |
| `service_categories` | `name`, `slug`, `sort_order` |
| `service_tiers` | `service_category_id`, `name`, `price`, `billing`, `features` (json), `popular`, `cta`, `sort_order` |
| `tech_packages` | `category`, `icon`, `title`, `version`, `type`, `description`, `price` (nullable), `billing`, `command`, `features` (json), `install_commands` (json), `repo_url`, `docs_url`, `sort_order` |
| `blog_posts` | `user_id`, `title`, `slug`, `excerpt`, `body`, `tags` (json), `cover_image`, `featured`, `published`, `published_at` |
| `faqs` | `question`, `answer`, `sort_order` |
| `timeline_phases` | `days`, `title`, `description`, `sort_order` |
| `contact_messages` | `user_id?`, `name`, `email`, `phone?`, `subject?`, `message`, `is_read` |
| `invoices` | `user_id`, `service_tier_id?`, `tech_package_id?`, `invoice_no` (unique), `amount`, `currency`, `status`, `payment_method`, `purchased_at` |
| `settings` | `key` (unique), `value` (JSON-encoded) |
| `activity_logs` | `user_id?`, `action`, `details` (json) |
| `support_conversations` | `user_id?`, `session_id?`, `title`, `status` (open/closed), `source` (hud/panel/admin), `last_message_at` |
| `support_messages` | `support_conversation_id`, `role` (user/assistant), `body` |

## 7. Design System

Full reference in [DESIGN.md](DESIGN.md). Summary:

- Fonts: Orbitron (display), Rajdhani (body), mono kickers.
- Core colors: `night #05070f`, `panel #091322`, `cyanx #2af6ff`, `magx #ff3a98`, `violetx #9657ff`.
- 10 HUD themes (`hud-chrome.js`): dark cyan/magenta/violet/emerald/amber + light ice/slate/pearl/lavender/sand.
- Components: HUD buttons, cards, chips, inputs, section titles, kickers, corner accents,
  electric frame, pricing cards, skill bars, timeline, FAQ, modals, assistant panel, particles,
  matrix background, copy-toast.
- Motion respects `prefers-reduced-motion`; heavy animations are culled on mobile.
- Secondary surfaces: auth layout, customer panel (`panel.css`), admin console (`admin-enhanced.css`).

## 8. Security

- Laravel authentication with rate-limited login (5 attempts/min per email + IP).
- Admin routes behind `auth` + `admin` + active middleware; customer panel behind `auth` + active.
- Eloquent ORM exclusively (parameterized queries); passwords hashed.
- Blade escaping everywhere except intentionally-rendered HTML (blog body).
- Activity logging for admin mutations and auth events.
- Known findings & remediation: see [SECURITY_AUDIT.md](../SECURITY_AUDIT.md) (trusted proxies,
  HTML sanitization, rate limiting on register/contact/assistant, password recovery, security headers).

## 9. Testing

- PHPUnit feature tests (10 classes, ~100 cases): public home renders, blog list/show,
  register, login, throttling, customer panel access control, admin access control, invoice
  filtering, checkout for both services and packages, payment-method validation, admin CRUD,
  settings/invoice management, AI assistant + provider config.
- Run with `composer test` (in-memory SQLite).

## 10. Out of Scope / Future

- Additional gateway providers (PayPal, …) beyond the shipped set (Stripe, Binance Pay B2B, bKash, Razorpay, SSLCommerz, AmarPay).
- Email verification & password reset flows.
- Comment system, media library, analytics dashboards.
- Real-time notifications.
- Settings caching (the `Setting::get` path is currently uncached).

## 11. Demo Credentials

Seeded by `php artisan db:seed --force`. Admin password can be overridden with `ADMIN_SEED_PASSWORD`.

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@scifi-hud.test` | `password` |
| Admin | `ops@scifi-hud.test` | `password` |
| Customer | `client@scifi-hud.test` | `password` |
| Customer | `alex@scifi-hud.test` | `password` |
| Customer (deactivated) | `maria@scifi-hud.test` | `password` |
