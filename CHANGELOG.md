# Changelog

All notable changes to the **Sci-Fi HUD Portfolio** project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-08

### Added

- **Public portfolio site** — glitch hero, typewriter subtitle, skills matrix, project previews,
  services with category tabs, tech/package command center with detail modals, project timeline,
  FAQ accordion, blog (search + tags), contact inbox, and a 10-theme HUD switcher.
- **Customer portal** (`/panel`) — dashboard, invoices with status filters, purchased services &
  package licenses, support chat with the site owner, profile & password management.
- **Admin console** (`/admin`) — dashboard with revenue/activity feed; CRUD for users, projects,
  skills, services, packages, blog, FAQs, timeline; order management (pay / cancel / refund);
  message inbox; settings for Site, SEO, Appearance, AI Assistant and Payments.
- **Authentication** — register with live validation, rate-limited login, logout, deep-linked
  checkout after signup (`?plan=` / `?pkg=`), active-user enforcement middleware.
- **Checkout & payments** — manual channels (bKash, Nagad, Binance P2P, PayPal) and gateway
  processing (Stripe, Binance Pay B2B, bKash, Razorpay, SSLCommerz, AmarPay) with signed webhooks
  and a pending-invoice lifecycle.
- **AI support assistant** — floating HUD chat widget backed by any OpenAI-compatible endpoint,
  with an offline keyword fallback (`USER_LLM_*` env vars).
- **Community feed** — media uploads (images, video, documents) with extension + content-sniff
  validation and sandboxed document serving.
- **Testing** — PHPUnit feature suite covering public pages, auth & access control, blog,
  checkout, payments, admin CRUD, settings and the AI assistant.
- **Documentation** — `README.md`, `docs/PRD.md`, `docs/ARCHITECTURE.md`, `docs/DESIGN.md`,
  `SECURITY_AUDIT.md`, `DEPLOY_CPANEL.md`.

### Notes

- First public release of the codebase on GitHub.
