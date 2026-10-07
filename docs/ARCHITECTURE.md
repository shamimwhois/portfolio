# Architecture — Sci-Fi HUD Portfolio

A full-stack analysis of the project: stack, structure, routing, data model, Livewire
components, key flows, and testing strategy. Companion docs: [PRD.md](PRD.md) (requirements),
[DESIGN.md](DESIGN.md) (design system), [`../SECURITY_AUDIT.md`](../SECURITY_AUDIT.md) (security).

---

## 1. Overview

**Laravel 13 · Livewire 4 · Blade · SQLite · Tailwind CSS.** The application is a database-driven
rewrite of a static HUD portfolio. Every public section, plus the customer portal and admin
console, is a Livewire component backed by Eloquent models. Configuration is largely
**settings-as-config**: site identity, SEO, theme, section visibility, payments, and the AI
assistant are all stored in the `settings` key/value table rather than hardcoded.

### Conventions

- **Livewire 4 attribute style** — `#[Layout]`, `#[Computed]`, `#[Url]`, `#[Rule]` class attributes.
- **Eloquent** — classic `protected $fillable` / `$casts` on most models; the `User` model uses the
  newer `#[Fillable]` / `#[Hidden]` attributes.
- **Admin CRUD pattern** — every admin component follows `create()/edit($model)/cancel()/save()/delete($model)`
  with `showForm` / `editingId` flags, inline validation, and `ActivityLog::record('admin.<entity>.<verb>', ...)`.
- **Blade components** — `x-hud.nav`, `x-panel.sidebar`, `x-admin.sidebar`, `x-icon`, layouts under `components/layouts/`.
- **Static assets** — `public/assets/` (not Vite-managed); Vite + Tailwind 4 exist for compiled entry points (`resources/`).

## 2. Directory Structure

```
app/
├── Http/Middleware/
│   ├── AdminAccess.php            # 403 unless auth()->user()->isAdmin()
│   └── EnsureUserIsActive.php     # logout + redirect deactivated users
├── Livewire/
│   ├── Admin/                     # Console CRUD + Settings (Site, Seo, Appearance, Ai, Payments)
│   ├── Auth/                      # Login, Register
│   ├── Blog/                      # Index (search/tag), Show (route-bound)
│   ├── Contact.php                # Public contact form
│   ├── Home/Index.php             # Home sections orchestration
│   ├── Panel/                     # Dashboard, Invoices, Services, Support, Profile
│   ├── Sections/                  # Packages, Services (public checkout widgets)
│   └── Support/Assistant.php      # Floating HUD chat widget
├── Models/                        # 17 models (14 tables + auth scaffolding)
├── Services/Ai/                   # Client, Message, AiException — Chat Completions
├── Support/                       # Payments, Nav, AiConfig, SupportAssistant
└── Providers/AppServiceProvider.php  # singleton Ai\Client from AiConfig::all()
config/
├── payments.php                   # Manual channels + gateway presets (fallback seed)
├── llm.php                        # LLM providers + env-driven connection
└── nav.php                        # Public/panel/admin navigation definitions
database/
├── migrations/                    # 11 migrations (schema + additive updates)
├── factories/UserFactory.php
└── seeders/DatabaseSeeder.php     # Users, settings, content, engagement, invoices, support
public/assets/                     # hud.css, panel.css, admin-enhanced.css, auth.css, hud.js, hud-chrome.js, pic.jpg
resources/views/                   # Blade layouts, components, Livewire views
routes/web.php                     # All web routes (no API/console routes of note)
tests/                             # PHPUnit: 10 feature classes + example unit test
```

## 3. Bootstrap & Middleware (`bootstrap/app.php`)

```php
$middleware->alias([
    'admin' => AdminAccess::class,
    'user.active' => EnsureUserIsActive::class,
]);
$middleware->trustProxies(at: '*');   // ⚠️ see SECURITY_AUDIT C2 — restrict in production
```

- **`AdminAccess`** — `abort(403)` unless the authenticated user `isAdmin()`.
- **`EnsureUserIsActive`** — logs out deactivated users and redirects to `login` with an error.
- Route groups: panel = `['auth', EnsureUserIsActive::class]`; admin = `['auth', 'admin', EnsureUserIsActive::class]`.

## 4. Routing Map (`routes/web.php`)

| Group | Routes |
| --- | --- |
| **Public** | `GET /` → `Home\Index`; `GET /blog` → `Blog\Index`; `GET /blog/{post:slug}` → `Blog\Show`; `GET /contact` → `Contact` |
| **Guest** | `GET /login` → `Auth\Login`; `GET /register` → `Auth\Register`; `POST /logout` (closure) |
| **Panel** (`auth` + active, prefix `/panel`) | `/` dashboard · `/invoices` · `/projects` · `/projects/{project}` · `/projects/{project}/media/{media}` (owner-or-admin) · `/projects/{project}/files/{file}` (owner) · `/orders/new` · `/orders/{project}/edit` (own pending brief) · `/services` · `/support` · `/profile` |
| **Admin** (`auth` + admin + active, prefix `/admin`) | `/` dashboard · `/users` · `/projects` · `/skills` · `/services` · `/packages` · `/blog` · `/faqs` · `/invoices` · `/timeline` · `/messages` · `/conversations` · `/settings/{site,seo,appearance,ai,payments}` (`/settings` redirects to site) |

## 5. Data Model

### `users` (extended Laravel default)
`name`, `email` (unique), `email_verified_at`, `password` (hashed cast), `role` (default `customer`),
`avatar`, `phone`, `bio`, `is_active` (default true). Roles: `User::ROLE_ADMIN = 'admin'`,
`User::ROLE_CUSTOMER = 'customer'`. Relations: `invoices()`, `posts()`, `logs()`.
Helpers: `isAdmin()`, `isCustomer()`. *No `username` column — removed via migration
`2026_08_06_000001_drop_username_from_users`.*

### Content
| Model | Table | Notable columns |
| --- | --- | --- |
| `Skill` | `skills` | `name`, `level` (0–100), `icon`, `sort_order` |
| `Project` | `projects` | `title`, `description`, `link`, `thumbnail`, `tech` (json), `status`, `featured`, `sort_order` |
| `ServiceCategory` | `service_categories` | `name`, `slug` (unique), `sort_order`; `tiers()` hasMany |
| `ServiceTier` | `service_tiers` | `service_category_id` (FK), `name`, `price` (string, e.g. `$499`), `billing` (`one-time|month`), `features` (json), `popular`, `cta` (`outline|gradient`), `sort_order` |
| `TechPackage` | `tech_packages` | `category` (javascript/php/python/managers), `icon`, `title`, `version`, `type`, `description`, `price` (nullable decimal), `billing`, `command`, `features` (json), `install_commands` (json), `repo_url`, `docs_url`, `sort_order` |
| `BlogPost` | `blog_posts` | `user_id` (FK), `title`, `slug` (unique), `excerpt`, `body` (longText, raw HTML), `tags` (json), `cover_image`, `featured`, `published`, `published_at` |
| `Faq` | `faqs` | `question`, `answer`, `sort_order` |
| `TimelinePhase` | `timeline_phases` | `days`, `title`, `description`, `sort_order` |

### Engagement & commerce
| Model | Table | Notable columns |
| --- | --- | --- |
| `ContactMessage` | `contact_messages` | `user_id?` (FK), `name`, `email`, `phone?`, `subject?`, `message`, `is_read` |
| `Invoice` | `invoices` | `user_id` (FK), `service_tier_id?` (FK), `tech_package_id?` (FK), `invoice_no` (unique), `amount` (decimal 10,2), `currency`, `status` (`pending|paid|refunded|cancelled`), `payment_method`, `purchased_at` |
| `ActivityLog` | `activity_logs` | `user_id?` (FK), `action`, `details` (json); `ActivityLog::record(action, user, details)` |
| `Setting` | `settings` | `key` (unique), `value` (JSON-encoded) |
| `SupportConversation` | `support_conversations` | `user_id?`, `session_id?`, `title`, `status` (`open|closed`), `source` (`hud|panel|admin`), `last_message_at`; `ownedBy(userId, sessionId)` scope |
| `SupportMessage` | `support_messages` | `support_conversation_id` (FK, cascade), `role` (`user|assistant`), `body` |

### Client projects & orders (Upwork-style intake)
| Model | Table | Notable columns |
| --- | --- | --- |
| `ClientProject` | `client_projects` | `user_id` (FK), `invoice_id?`, `title`, `description`, `status` (`pending|in_progress|review|completed|cancelled`), `source` (`admin|client`), `submitted_at?`, `tech_package_id?` / `service_tier_id?` (FKs), `start_date`, `deadline`, `remind_at`, `requirements`, `agreement`, `policy`, `cancel_policy`, `return_policy`, brief: `example_url?`, `demo_url?`, `design_notes?`, `color_palette?` (json hex array), `budget_from/to?`, `timeline_preference?`, `preferred_deadline?`, offer: `offer_price?`, `offer_note?`, `offer_status?` (`pending|accepted|declined`). Helpers: `isClientSubmitted()`, `isEditableByCustomer()`, `offerPending/Accepted/Declined()`, `budgetLabel()`, `timelinePreferenceLabel()`, `progress()`. Deleting event removes stored files from disk. |
| `ProjectTask` / `ProjectFeature` | `project_tasks` / `project_features` | `client_project_id` (FK, cascade), `title`, `is_complete`, `sort_order` |
| `ProjectUpdate` | `project_updates` | `client_project_id` (FK), `user_id?`, `type` (`update|milestone|note|reminder`), `message` |
| `ProjectFile` | `project_files` | `client_project_id` (FK), `name`, `path` (local disk), `size`, `mime`, `kind` (`deliverable` = team → customer downloads, `attachment` = customer brief uploads) |
| `ProjectMedia` | `project_media` | `client_project_id` (FK, cascade), `type` (`image|video`), `path`, `position`; served via `GET /panel/projects/{project}/media/{media}` (owner-or-admin) |

### Community feed (Facebook-style)
| Model | Table | Notable columns |
| --- | --- | --- |
| `FeedPost` | `feed_posts` | `user_id` (FK, cascade), `body` (text — media-only posts allowed); `media()`, `reactions()`, `reactionBy(userId)`, `reactionTotals()` |
| `FeedComment` | `feed_comments` | `user_id` (FK), `feed_post_id` (FK, cascade), `parent_id?` (self-FK, replies flattened to two levels), `body`; `reactions()` |
| `FeedLike` | `feed_likes` | morphable `likeable` (post/comment), `reaction_type` (`like|love|haha|wow|sad|angry`); unique `(user_id, likeable_type, likeable_id, reaction_type)` |
| `FeedMedia` | `feed_media` | `feed_post_id` (FK, cascade), `type` (`image|video`), `path` (local disk), `position`; served via `GET /feed/media/{media}` |

### Cross-cutting helpers
- **`Setting::get($key, $default)`** — dot-notation (`seo.meta_title`, `sections.hero`), JSON-decodes values, falls back to defaults. **`Setting::set`** upserts JSON. ⚠️ Uncached (one query per read — see SECURITY_AUDIT Q1).
- **`Invoice::itemLabel()`** — resolves `category // tier` or `category // package` or "Custom order"; `paymentMethodLabel()/Account()` resolve via `Payments`.
- **`TechPackage::isSellable()`** — `price !== null`; `categoryLabel()`, `displayPrice()`, `billingLabel()`.
- **`BlogPost::excerptOrFallback($length)`** — strips tags, limits body.

## 6. Livewire Component Inventory

### Public
| Component | Responsibility |
| --- | --- |
| `Home\Index` | Renders all home sections gated by `sections.*` settings; supplies skills, featured projects, phases, FAQs, stats, typewriter lines. |
| `Sections\Packages` | Tabbed package grid; `purchase()` opens checkout (guest → `register?pkg=`), `confirmPurchase()` creates a pending invoice for sellable packages; blocks duplicate licenses. |
| `Sections\Services` | Category tabs → tier pricing; `purchase()` opens checkout (guest → `register?plan=`), `confirmPurchase()` creates a pending invoice; `hasActiveOrder()` blocks duplicates. |
| `Blog\Index` | `#[Url]` `search` + `tag`; `#[Computed] posts` (published, LIKE + `whereJsonContains`), `allTags()`. |
| `Blog\Show` | Route-bound `BlogPost`; aborts 404 if unpublished; related posts. |
| `Contact` | Pre-fills auth'd user; persists `ContactMessage`, success state. |
| `Feed\Index` | Public community feed — read-only for guests (login prompts instead of controls). Auth users post text/image(s)/video (`WithFileUploads`, max 6 images / one video), react with 6 emoji types, comment/reply, edit/delete own posts (admin too), share via social links. Media opens in an Alpine fullscreen lightbox. |
| `Support\Assistant` | Floating widget; session/user-scoped conversations; quick prompts; calls `SupportAssistant::answer()`. |

### Auth
| Component | Notes |
| --- | --- |
| `Auth\Login` | Throttle `login:{email}|{ip}` (5/min); session regenerate; `auth.login` log; role-based redirect. |
| `Auth\Register` | `#[Url]` `plan`/`pkg` deep links; creates customer, fires `Registered`, logs in, session regenerate, redirects to origin section or panel. |

### Panel (`auth` + active)
| Component | Notes |
| --- | --- |
| `Panel\Dashboard` | Stats (invoices, paid total, active services, tickets), recent invoices + blog. |
| `Panel\Invoices` | Status filter (`cancelled` maps to refunded+cancelled); per-status counts. |
| `Panel\Projects` | Source filter (`all|client|admin`) + status chips; cards show progress, deliverables/media counts, client-brief badge and pending-offer price; “Start a Project” CTA + empty state. |
| `Panel\ProjectDetail` | Polled live view (progress, tasks, features, updates, deliverables). For client briefs: “Your brief” card (links, design notes, color swatches, budget, timeline), submitted-materials gallery (images/video/docs), **budget offer card** with `acceptOffer()`/`declineOffer()`, and `cancelOrder()` + “Edit brief” link while the order is pending. |
| `Panel\Order` | 6-step wizard (`handleSubmit` is step-aware): basics (title/description/package or service reference/timeline) → requirements → references & design (example/demo URLs, design notes, color palette ≤5) → budget range → materials (`docs` ≤5 PDF/DOCX/etc 20MB, `images` ≤6, `video` one ≤64MB via `WithFileUploads`) → review & submit. Creates `source=client`, `status=pending`; edit mode (`/orders/{id}/edit`) only for own pending orders and resets any outstanding offer; uploads land as `ProjectFile kind=attachment` + `ProjectMedia`. |
| `Panel\Services` | `#[Computed] activeServices` from paid invoices. |
| `Panel\Support` | Conversation CRUD-lite: new/open/send/close; ownership enforced (`abort(404)` for foreign conversations). |
| `Panel\Profile` | Update profile; change password (current-password check). ⚠️ No `logoutOtherDevices()` (SECURITY_AUDIT H5). |

### Admin
| Component | Notes |
| --- | --- |
| `Admin\Dashboard` | Aggregates counts, pending orders, messages, activity feed. |
| `Admin\Users` | CRUD + search + role/active toggles; self-protection guards (no self role change/delete/deactivate). ⚠️ Last-admin guard missing (SECURITY_AUDIT M3). |
| `Admin\Projects` / `Skills` / `Faqs` / `Timeline` | Standard CRUD with `sort_order`. |
| `Admin\Services` | Dual CRUD: categories + tiers. |
| `Admin\Packages` | CRUD with price/billing, install commands, feature lists. |
| `Admin\Blog` | CRUD with auto-slug, tags, publish toggle + `published_at`. |
| `Admin\Invoices` | `markPaid` / `cancel` / `refund` with state guards; status filter + search. |
| `Admin\ClientProjects` | CRUD + detail manager (tasks/features/updates, deliverables upload `kind=deliverable`, customer-materials review with delete). Source filter; for client briefs: customer brief card + **budget negotiation** — `negotiateBudget()` sends `offer_price`/`offer_note` (`offer_status=pending`), `clearOffer()`; customer accepts/declines from their panel. |
| `Admin\Messages` | Inbox read/unread/delete. |
| `Admin\Conversations` | Support threads: reply (reopens), close, reopen. |
| `Admin\Settings\Site` | Identity, hero, about, typewriter, stats (JSON validated), contact/social. |
| `Admin\Settings\Seo` | Nested `seo` array. |
| `Admin\Settings\Appearance` | `theme_default` (whitelisted 10), `theme_switcher`, `sections` visibility. |
| `Admin\Settings\Ai` | Provider presets, `ai_provider`, credentials, `testConnection()`. |
| `Admin\Settings\Payments` | Currency, gateway provider/mode/credentials, method enable/disable. |

## 7. Key Flows

### 7.1 Checkout (service tier or tech package)
1. Guest clicks **Buy license / Select plan** → redirected to `register?pkg=ID` / `register?plan=ID`; after signup they land back on the section with checkout open.
2. Customer picks a payment method (from `Payments::enabledMethods()`) in the checkout summary.
3. `confirmPurchase()` validates the method, **dedupes active orders**, and creates an invoice:
   `invoice_no = INV-YYYY-####` (⚠️ `max(id)+1` — race-prone, SECURITY_AUDIT H3), `status = pending`, `purchased_at = now()`, currency from config.
4. Admin later confirms manually: `markPaid` / `cancel` / `refund` (with status guards), each audited via `ActivityLog`.
5. Paid invoices surface in **Panel → Services** (install commands for packages) and in admin revenue stats.

### 7.2 Settings-as-config
`Setting` maps a key → JSON value. Read anywhere with `Setting::get('sections.services', true)` or
dotted paths (`Setting::get('seo.meta_title')`). Admin Settings components write whole nested
structures (`Setting::set('seo', [...])`, `Setting::set('payments', [...])`). Home sections, theme
default, SEO meta, AI enablement, and payment channels are all driven this way.

### 7.3 AI Support Assistant
1. Widget visibility gated by `ai_enabled`. Guests get a conversation keyed on `session_id`; customers on `user_id`.
2. `SupportAssistant::answer()` persists the user message, calls `generate()`, persists the reply, stamps `last_message_at`, and audits `support.answered`.
3. `generate()` → if `Client::isConfigured()` → `chat()` with **system prompt + knowledge + last 8 messages**; on `AiException` falls back to `fallbackReply()` (keyword matching for pricing/delivery/packages/order status).
4. `AiConfig::all()` resolves effective config: **admin-saved `ai_config` → provider preset → env (`config/llm.php`)**. Providers: OpenAI, DeepSeek, OpenRouter, Mistral, Ollama (local), LM Studio (local), custom.
5. Admin can **test connection** from Settings → AI.

### 7.4 Activity Logging
`ActivityLog::record(action, ?User, details)` fires on auth (`auth.login`, `auth.register`), checkout (`invoice.requested`, `package.purchased`), support (`support.answered`, `support.replied`), and all admin mutations (`admin.<entity>.<verb>`). Rendered on the admin dashboard feed.

### 7.4b Order intake & budget negotiation
1. Customer opens **Panel → Start a Project** (`Panel\Order`, 6-step wizard). Each step validates its fields; Enter/Submit advances via `handleSubmit` (full submit only on step 6).
2. `submit()` validates steps 1–5, then creates `ClientProject` (`source=client`, `status=pending`, `submitted_at`) or updates the customer's own pending brief (resetting `offer_*`). Uploads are persisted as `ProjectFile kind=attachment` and `ProjectMedia` (image/video) under `storage/app/private/client-projects/{id}/`; `panel.projects.media` serves them to owner-or-admin.
3. Admin reviews the brief in **Admin → Client Projects** (source filter “Client briefs”), sees example/demo links, design notes, color swatches, budget range and customer materials, then sends a budget offer (`negotiateBudget` → `offer_status=pending`).
4. Customer sees the offer card on the project detail page and can `acceptOffer()` (locks the price) or `declineOffer()`; they can also edit the brief (offer resets) or cancel the pending order (`status=cancelled`). Once accepted, the admin advances status to `in_progress` and the normal tasks/features/updates/deliverables pipeline takes over.
5. Deleting a project triggers a model `deleting` event that removes all stored media/attachment/deliverable files from disk.

### 7.4c Community Feed
1. Guests can **read** posts/comments; every write action (post, react, comment, reply, edit) is gated server-side by `requireAuth()` (redirect to `login?redirect=/feed`) and the UI swaps controls for login-prompt cards. `openComment()` is intentionally ungated so guests can read threads.
2. `createPost` validates `body` (nullable) **or** media: `images` (≤6, jpeg/png/webp/gif ≤10MB) and `video` (mp4/webm/ogg/ogv/mov ≤64MB) via `WithFileUploads`; files `store('feed/{postId}', 'local')` → `feed_media` rows; live previews use `temporaryUrl()`.
3. `reactToPost`/`reactToComment` toggle the user's reaction (`FeedLike::TYPES` whitelist); picking a different type switches it (`updateOrCreate` on user+likeable). `reactionTotals()`/`reactionBy()` read the eager-loaded relation (no N+1 — `posts()` loads `reactions`, `media`, and comments with `reactions`).
4. `deletePost` deletes the media files from disk + reaction rows, then the post (comments cascade via FK). `FeedPostPolicy` gates `update`/`delete` to owner-or-admin.
5. Media is served by `GET /feed/media/{media}` → `response()->file()` (404 when missing); `Js::from()`-built item arrays drive the Alpine `mediaViewer` lightbox (prev/next, arrows, Escape, scroll-lock).

### 7.5 RBAC & Nav
`Nav::visibleForRole()` filters `config/nav.php` items by `role` (any/guest/auth/admin/customer);
`Nav::href()` resolves `route()` + `#anchor`. The same config powers the site header, footer,
panel sidebar, and admin sidebar.

## 8. Services & Support Classes

| Class | Role |
| --- | --- |
| `Services\Ai\Client` | Final class; POSTs `/chat/completions` (bearer auth, timeout). `isConfigured()` = base_url + (api_key or local). Throws `AiException` on connection failure, non-2xx, or missing content. |
| `Services\Ai\Message` | Immutable `{role, content}` with `system()/user()/assistant()` factories. |
| `Support\Payments` | `all()` (settings or config fallback), `currency()`, `methods()`, `enabledMethods()`, `isEnabled()`, `isGateway()`, `gateway($key)`, `gateways()`, `gatewayMode($key)`, `isGatewayAvailable($key)`, `credential($key, $field)`. |
| `Support\Nav` | `visible()`, `visibleForRole()`, `href()`. |
| `Support\AiConfig` | Provider presets + config precedence resolution. |
| `Support\SupportAssistant` | Chat orchestration, system prompt, knowledge builder, keyword fallback. |

## 9. Frontend & Build

- **Static CSS/JS** in `public/assets/` are the primary design system (see [DESIGN.md](DESIGN.md)).
- **Vite + Tailwind 4** (`vite.config.js`, `resources/css/app.css`, `resources/js/app.js`) exist as a
  compiled entry point; the default `welcome.blade.php` uses it, but all app layouts use the CDN
  Tailwind + static assets route.
- `composer dev` runs serve + queue + pail + vite concurrently (via `npx concurrently`).

## 10. Testing Strategy

`phpunit.xml`: in-memory SQLite, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`,
`QUEUE_CONNECTION=sync`, `BCRYPT_ROUNDS=4`. Run: `composer test`.

| Test class | Covers |
| --- | --- |
| `PublicPagesTest` (6) | Home sections render, blog index/show + unpublished 404, contact/login/register render |
| `AuthAccessTest` (10) | Guest/customer/admin redirects & 403s, all 16 admin sections, inactive-user gate, panel sections |
| `EngagementTest` (9) | Contact persistence, registration, invoice ownership isolation, unread messages |
| `PaymentMethodTest` (11 / 17 cases) | Data-provider checkout per method, invalid method rejection, cancel, gateway mode, panel labels |
| `PackagePurchaseTest` (8) | Guest deep-link, purchase, duplicate-block, reference package block, admin pricing, panel services |
| `PanelOrderTest` (15) | Order wizard submission (brief + docs/images/video persisted with kinds), step validation, budget gte, wizard navigation renders every step, edit own pending order (offer reset), ownership 403s (foreign / non-pending), cancel, admin offer → accept/decline, media route owner/admin/403, project delete cleans disk |
| `ClientProjectTest` (10) | Admin project CRUD, tasks/features/updates/uploads, invoice-paid auto project, customer isolation, detail rendering, downloads + 403 |
| `AdminCrudTest` (11) | Full CRUD cycles + self-protection guards for every content entity |
| `AdminSettingsAndInvoicesTest` (26) | Payments/gateway helpers, settings saves, theme/SEO/visibility on homepage, invoice state machine |
| `AiSupportTest` (13) | Client behavior, assistant persistence, fallback knowledge, ownership isolation, admin reply |
| `AiProviderTest` (6) | AiConfig precedence, provider saves, test-connection ok/fail, local providers |
| `ExampleTest` (1) | Sanity: `config('app.name') === 'Sci-Fi HUD Portfolio'` |

**Gaps** (from `SECURITY_AUDIT.md` §5): unit tests for `Setting`/`Payments`/`Nav`/`Invoice` helpers
don't exist yet; no tests for password recovery (H4), session invalidation (H5), or last-admin guard (M3).

## 11. Known Gaps & Next Steps

Security remediation (prioritized) lives in [`../SECURITY_AUDIT.md`](../SECURITY_AUDIT.md):
- Restrict `trustProxies` (C2), sanitize blog body (C3).
- Rate-limit register/contact/assistant (H1), validate enabled methods only (H2), atomic invoice numbers (H3), password recovery (H4), session invalidation on password change (H5), tighter validation rules (H6).
- Security headers (M1), seeder credential hardening (M2), last-admin guard (M3), middleware ordering (M4), email verification decision (M5), AI prompt-injection guard (M6), broader exception handling (M7), env hygiene (M8).

Code quality: cache `Setting::get` (Q1), extract a shared `CheckoutService` to de-duplicate
`Sections\Services` / `Sections\Packages` (Q2), unify model style on `User` (Q3), custom error
pages (Q4), remaining validation (Q5).
