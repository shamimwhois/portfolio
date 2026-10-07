# Design System — Sci-Fi HUD Portfolio

**Scope:** Public HUD (`hud.css`, `hud.js`, `hud-chrome.js`), auth, customer panel (`panel.css`), and admin console (`admin-enhanced.css`).
**Design language:** "Mission control" — every screen reads like an operator dashboard. Form follows signal: neon glows mark interactive intent, HUD frames delimit content, motion communicates state.

---

## 1. Design Philosophy

The system is built on four principles:

1. **Functional futurism** — aesthetics never outweigh legibility. Glows mark actions, scan-lines imply data flow, but body copy stays high-contrast and readable (Rajdhani at comfortable sizes).
2. **Signal over decoration** — every visual element maps to a purpose: corner brackets frame viewports, chips tag metadata, the timeline spine shows progression, particles add depth without competing with content.
3. **Themable chrome** — the entire surface is driven by CSS custom properties (`--line`, `--hot`, `--violet`, `--bg-0`, `--bg-1`…). 10 themes restyle the full system at runtime without touching markup.
4. **Motion that respects the user** — `prefers-reduced-motion: reduce` disables animation wholesale; on mobile the heaviest GPU animations (electric frame, floating, spine, node pulse) are culled for stability and battery.

## 2. Design Tokens

### Core palette (public HUD)

| Token | Hex | Role |
| --- | --- | --- |
| `--bg-0` | `#05070f` | Base background (near-black navy) |
| `--bg-1` | `#091322` | Raised background / panels |
| `--line` | `#2af6ff` | Primary accent — cyan neon (borders, links, active states) |
| `--hot` | `#ff3a98` | Secondary accent — magenta (CTAs, glitch ghost, popular badges) |
| `--violet` | `#9657ff` | Tertiary accent — violet (gradients, subtitle, tertiary dots) |
| `--text` | `#dce7ff` | Primary text |
| `--muted` | `#95a4c9` | Secondary text / labels / kickers |
| `--surface` | `rgba(10,16,28,0.92)` | Card / panel surface |
| `--surface-elevated` | `rgba(14,22,38,0.95)` | Elevated surface (modals, stat cells) |
| `--line-soft` | `rgba(42,246,255,0.32)` | Soft borders (cards, buttons) |
| `--border-subtle` | `rgba(42,246,255,0.18)` | Subtle dividers |

Light themes override `--text`, `--muted`, `--surface*` and re-map `--line-soft`/`--border-subtle`
to `color-mix()` of the theme's accent.

### Tailwind bridge

CDN Tailwind extends the palette so Blade views can mix utilities and tokens:

```js
colors: { night: "#05070f", panel: "#091322", cyanx: "#2af6ff", magx: "#ff3a98", violetx: "#9657ff" }
```

## 3. Typography

| Face | Weights | Use |
| --- | --- | --- |
| **Orbitron** (Google Fonts) | 500, 700, 800 | Display — hero name, section titles, headings, pricing, stat values, buttons, brand |
| **Rajdhani** (Google Fonts) | 400, 500, 600, 700 | Body text, descriptions, nav |
| **ui-monospace / Cascadia Code** | — | Kickers (`// mission profile`), code blocks, command text |

Patterns:

- **Hero name** — Orbitron 800, `clamp(1.35rem, 4.5vw, 3.35rem)`, gradient text (`--line → --violet → --hot`), letter-spacing `0.06em`.
- **Section titles** (`section-title`) — Orbitron, uppercase, `clamp(1.3rem, 3vw, 1.8rem)`, letter-spacing `0.14em`, cyan.
- **Split headings** (`split-heading`) — Orbitron 800, `clamp(1.75rem, 4vw, 2.75rem)`, gradient text with the gradient split at 48%/52% (cyan→magenta), letter-spacing `0.12em`.
- **Kickers** (`hud-kicker`) — mono, `0.75rem`, letter-spacing `0.2em`, muted.
- **Timeline / pricing labels** — Orbitron, small caps with wide tracking (`0.1em`–`0.2em`).

## 4. Themes (10)

Source: `public/assets/js/hud-chrome.js` (`HUD_THEMES`) + `hud.css`. Applied via `data-theme` on `<body>`; persisted in `localStorage` (`hud.theme`); the default is driven by the `theme_default` setting.

| Theme | `--bg-0` | `--bg-1` | `--line` | `--hot` | `--violet` |
| --- | --- | --- | --- | --- | --- |
| `dark_cyan` | `#05070f` | `#091322` | `#2af6ff` | `#ff3a98` | `#9657ff` |
| `dark_magenta` | `#120612` | `#201029` | `#ff61d8` | `#ff9351` | `#ab7dff` |
| `dark_violet` | `#080512` | `#12142b` | `#9f80ff` | `#ff5d9b` | `#6cc8ff` |
| `dark_emerald` | `#05110e` | `#0a201b` | `#4ef8c5` | `#7df8ff` | `#63b9ff` |
| `dark_amber` | `#120f08` | `#231c11` | `#ffcb61` | `#ff8c53` | `#f4e57f` |
| `light_ice` | `#ecf8ff` | `#d7efff` | `#0b8dc8` | `#d45084` | `#6d62d6` |
| `light_slate` | `#eef1f6` | `#d8dfe8` | `#37658e` | `#a63f6a` | `#5a638a` |
| `light_pearl` | `#fff9f3` | `#f5ede4` | `#8f6a49` | `#9a4d68` | `#6f6aa8` |
| `light_lavender` | `#f8f2ff` | `#ece2fb` | `#7e60bf` | `#cc558e` | `#5f86ce` |
| `light_sand` | `#fff7ec` | `#f1e6d8` | `#9c7545` | `#ba5f51` | `#6a86ad` |

Light themes: matrix rain opacity drops to `0.08`, particles lose their glow, and the body background becomes a light gradient (`--bg-0 → --bg-1`).

## 5. Component Catalog

### 5.1 Backgrounds & Environment

| Component | Description |
| --- | --- |
| **Matrix rain** (`#matrixBg` / `.matrix-column`) | Fixed full-viewport layer of vertical katakana/digit columns, `writing-mode: vertical-rl`, cyan at ~30% opacity with neon text-shadow. Animated via `matrix-fall` (staggered duration/delay per column). Disabled under reduced motion. |
| **HUD grid** (`.hud-grid`) | Fixed 44px grid lines at `rgba(42,246,255,0.06)`, masked with a radial gradient so lines fade at the edges. |
| **Particles** (`#particles` / `.particle`) | 30 drifting specks in `--line` / `--hot` / `--violet`, `particleFloat` 6–14s loops, `box-shadow` glow. |
| **Corner accents** (`.corner-accent`) | Four fixed 60px L-brackets in `--line` at viewport corners, `opacity 0.5` once a theme is active. |
| **Section HUD frame** (`.section-hud-frame`) | Top/bottom gradient rules (cyan→magenta) with a sweeping highlight (`hudFrameLineSweep`) and pulsing bottom line (`hudFrameLinePulse`). |

### 5.2 Surfaces

- **`.hud-card`** — 1px `--line-soft` border, `linear-gradient(170deg, rgba(11,20,36,0.88), rgba(7,12,22,0.88))`, inner cyan glow + outer 35px halo, plus animated **corner brackets** via `::before`/`::after` (`sparkPulse`, 2.2s/2.8s). Used for skills, projects, FAQ, status flashes.
- **`.pricing-card`** — rounded 12px panel on `--surface`; the `.pricing-card--popular` variant gains a brighter border, cyan halo, and the rotated **"POPULAR" badge** (`--hot`, `rotate(-6deg)`, Orbitron 0.55rem).
- **`.about-panel` / `.about-stat`** — mission-profile panel with shield icon tile; stat cells on `--surface-elevated`, alternating cyan/magenta values.
- **`.preview-modal`** — native `<dialog>` framed in `--line-soft` with inset/outer glow; `::backdrop` dims at 70%. Used for project preview and package details.

### 5.3 Controls

- **`.hud-btn`** — uppercase Orbitron-ish (Rajdhani bold) 0.7rem, `--line-soft` border, cyan text, letter-spacing `0.12em`; hover lifts `translateY(-2px)` with a cyan glow and full-border.
- **`.hud-btn-alt`** — magenta variant (secondary CTA).
- **`.btn-gradient`** — solid `--line → --hot` gradient fill with dark text (primary pricing CTA).
- **`.tab-btn`** — package tabs; `.active` gets cyan border + glow.
- **`.service-tab`** — underline tabs; `.active` gets cyan underline.
- **`.hud-input` / `.hud-select`** — dark inputs on `rgba(7,13,24,0.86)` with `--line-soft` borders.
- **`.chip`** — tiny uppercase bordered tags (tech / status / package).
- **`.copy-btn`** — copies install commands; shows `.copy-toast` ("Command copied.").
- **`.faq-item summary`** — Orbitron cyan, native `<details>` disclosure.

### 5.4 Signature Elements

- **Electric frame** (`.electric-frame`) — the hero portrait: a hexagonal clipped (`clip-path: polygon(...)`) frame with 2px cyan border, diagonal scan stripes, layered cyan/violet glow, an **animated conic-gradient edge** (`frameEdgeSpin`, 8s) and pulsing charge (`frameEdgeCharge`, 3.2s). Wrapped in `.floating` (`floatDrift` 6s).
- **Glitch text** (`.glitch-text` / `.glitch`) — hero name with multi-layer neon text-shadow, `glitchPulse` 2s; JS scramble-reveal on load + hover jitter; CSS-only `::before`/`::after` chroma-split glitch (`g1`/`g2` clip-path keyframes) for static headings.
- **Typewriter** (`#typewriter`) — JS loop over `typewriter_lines` (75ms type / 50ms delete / 1s hold); renders as a single joined string under reduced motion.
- **Skill bars** (`.skill-bar` / `.skill-fill`) — 8px track, gradient fill (`--line → --violet`), width animated from `data-level` when scrolled into view (1.2s).
- **Timeline** (`.timeline-project-wrap` / `.timeline-phase`) — central glowing spine (`spineGlowSpark`), alternating left/right cards around 14px pulsing nodes (`nodePulse`); collapses to a single left rail under 900px.
- **Cursor / caret** — `.cursor-blink` (hero) and `.cursor-bar` (subtitle) `blink` 0.8s/1s.

### 5.5 Navigation & Shell

- **Site header** — sticky 3.35–4.35rem bar; desktop inline nav links (`nav-link` hover = cyan glow); mobile full-height slide-down panel with section groups, dividers, and 44px touch rows (`nav-mobile-panel`), toggled with backdrop blur.
- **Footer** — "Transmit Contact Signal" glitch heading, contact info, quick-access links (`config/nav.php` footer), social streams (Facebook, GitHub, Telegram, WhatsApp).
- **Assistant panel** (`.assistant-toggle` / `.assistant-panel`) — fixed bottom-right floating widget (320px, `max-height 60vh`), HUD-framed chat with user/assistant bubbles, quick-action chips, and a typing-friendly input. Z-index 61 above content, below nav.

## 6. Motion System

### Keyframe inventory (`hud.css`)

| Keyframe | Purpose | Duration |
| --- | --- | --- |
| `matrix-fall` | Rain column descent | 12–18s (staggered) |
| `particleFloat` | Particle ascent + scale | 6–14s (staggered) |
| `spin` | Loader rings / avatar glow | 1.2–1.6s |
| `sparkPulse` | Card corner brackets | 2.2s / 2.8s |
| `frameEdgeCharge` | Electric frame glow breathing | 3.2s |
| `frameEdgeSpin` | Conic edge rotation | 8s |
| `floatDrift` | Portrait float | 6s |
| `g1` / `g2` | CSS glitch clip-path slices | 2.9s / 2.2s |
| `blink` | Carets | 0.8s / 1s |
| `glitchPulse` | Hero text-shadow cycle | 2s |
| `subtitleFlicker` | Subtitle intermittent flicker | 4s |
| `heroNamePulse` / `heroScan` | Hero brightness / scan sweep | 4s / 2.8s |
| `hudFrameLineSweep` / `hudFrameLinePulse` | Section frame rules | 4.2s / 2.8s |
| `spineGlowSpark` | Timeline spine | 2.4s |
| `nodePulse` | Timeline nodes | 2s |
| `avatarSpin` / `pulse` | Panel avatar glow / status dot | 3s / 2s |
| `fadeIn` | Panel section swap | 0.3s |
| `brandGlow` | Admin brand | 3s |

### Scroll behavior

- `IntersectionObserver` reveals (`opacity/translateY` 0.72s, blur-out 0.95s on sections) with a `threshold 0.18` and mobile-aware root margins.
- Parallax on `[data-speed]` layers via rAF-throttled scroll, disabled on mobile and reduced motion.
- Skill fills animate to `data-level` on intersect; everything is forced visible when reduced motion is active.

### Audio (Arwes)

`hud-chrome.js` arms on first pointerdown and plays pooled click/assemble/type sounds from
`https://arwes.dev/assets/sounds/*.mp3`, throttled to 50ms, jittered volume/rate. **Silent under
`prefers-reduced-motion: reduce`** and never plays before user gesture.

### Reduced motion & mobile culling

- Global kill-switch: `@media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }` plus per-component overrides.
- Mobile: electric frame, floating, timeline spine/node, project-card 3D hover, and matrix effects are disabled or statically styled; touch targets are forced ≥44px and interactive elements prevent iOS auto-zoom (`font-size: 16px !important`).

## 7. Responsive Strategy

| Breakpoint | Behavior |
| --- | --- |
| `< 480px` | Compact hero, reduced paddings, one-column grids, smaller brackets |
| `< 640px` | Footer stacks, project cards single column |
| `< 768px` | Electric frame static, mobile nav panel, touch-optimized controls |
| `< 900px` | Timeline collapses to left rail |
| `< 1024px` | Hero stacks (portrait below text), desktop nav → mobile panel |
| `≥ 1024px` | Full HUD chrome, project-card 3D tilt hover |

`max-width: 7xl` containers with `px-4/6` gutters; `scroll-margin-top` offsets anchor links below the sticky header; `overscroll-behavior: contain` prevents scroll chaining.

## 8. Accessibility

- `prefers-reduced-motion` honored globally (animations, parallax, audio, reveals).
- Semantic HTML: `<details>/<summary>` FAQ, native `<dialog>` modals, `aria-hidden` on decorative layers (matrix, grid, particles, brackets).
- Mobile nav manages `aria-expanded`, Escape-to-close, body scroll lock, and focus-funneling to the first link.
- iOS auto-zoom prevention on inputs/buttons; ≥44px touch targets; `-webkit-tap-highlight-color: transparent`.
- Color contrast is designed for dark surfaces (`--text #dce7ff` on `#05070f`); light themes swap text tokens accordingly.
- Keyboard focus visible: `:focus-visible` adds cyan glow/borders across buttons, links, tabs, nav.

## 9. Secondary Surfaces

### Auth (`layouts/auth.blade.php` + `hud.css`)
Minimal HUD: matrix + grid, corner accents, centered card on `data-theme="dark_cyan"`, theme switcher, "Back to portfolio" link.

### Customer Panel (`panel.css`)
Operator-dashboard take on the HUD:

- **Shell** — fixed 260px sidebar (gradient navy, cyan border), sticky blurred header, content on a dark gradient; responsive: sidebar slides off-canvas under 768px.
- **Avatar glow** — conic-gradient ring (`avatarSpin` 3s) around initial-based avatars.
- **Status pill** — "Online" with pulsing cyan dot.
- **Stat cards** — `auto-fit minmax(240px, 1fr)` grid; cyan/magenta/violet icon tiles; hover lift.
- **Panel cards** — Orbitron cyan card titles with underline divider.
- **Billing** — plan feature tags, billing rows with paid status chips, payment method rows.
- **Security** — toggle switches, device list, password-change form.
- **Activity timeline** — dot-colored entries with timestamps.
- Reduced-motion: avatar glow, status dot, loader ring disabled.

### Admin Console (`admin-enhanced.css`)
Same tokens, denser chrome:

- **Sidebar** — fixed 260px with gradient brand mark (`brandGlow`), mono "Secure channel" kicker, scrollable nav with an animated 3px left indicator bar on hover/active, `<details>` group for Settings with children indent + chevron.
- **Header** — sticky, blurred, "Secure channel" eyebrow + Orbitron title; theme switcher; mobile menu toggle.
- **Cards** — rounded 16px `admin-card`, hover lift + glow; card titles get a gradient 4px slash (`::before`).
- **Stat cards** — 4-up grid (2-up @1200px, 1-up @768px), gradient top accent line on hover, icon scale on hover.
- **Activity list / quick actions** — compact rows and 2-column grid buttons with icon scale.
- **Footer** — "Theme syncs with the main HUD via local storage" + Portfolio // Blog // Logout.
- Reduced-motion & responsive rules mirror the panel surface.

## 10. Performance Notes

- Fixed layers (matrix, grid, particles) sit at `z-index: -1` / `0` and are `pointer-events: none`.
- GPU hints: `will-change` on reveals, floating, and electric frame; `translateZ(0)` / `backface-visibility: hidden` to prevent flicker.
- Heaviest animations are disabled on mobile to avoid repaint storms and scroll jank.
- `loading="lazy"` on imagery; single shared HUD chrome CSS/JS across layouts.
- Runtime theming uses CSS custom properties — theme switches restyle without re-render.

## 11. Asset Map

| Asset | Role |
| --- | --- |
| `public/assets/css/hud.css` | Core design system (public + auth) |
| `public/assets/css/panel.css` | Customer panel surface |
| `public/assets/css/admin-enhanced.css` | Admin console surface |
| `public/assets/css/auth.css` | Auth form tweaks |
| `public/assets/js/hud.js` | Motion engine (matrix, glitch, typewriter, reveal, parallax, particles, modals, mobile nav) |
| `public/assets/js/hud-chrome.js` | Theme switcher + audio pools (HudChrome API) |
| `public/assets/image/pic.jpg` | Hero portrait |
| `resources/views/components/layouts/*` | hud / auth / panel / admin shell layouts |
