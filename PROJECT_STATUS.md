# Project Status

## Version and branches

- Product version: v3
- Integration branch: `release/v3.0.0/main`
- Current feature: PORT-010 Three.js configuration and placeholder (issue #15, ready)
- Feature branch: `feature/PORT-010-threejs-config`
- Delivery mode: gated autonomous delivery
- Historical original: `release/v1.0.0`
- Archived React prototype: `archive/react-v2-prototype-2026-07-11`

## Current phase

**PORT-009 DELIVERED — PORT-010 READY**

Laravel now composes the public landing page, project index and project case studies as localized,
HTML-first Blade pages. The Lovable reference remains preserved under `resources/js/portfolio/`,
while essential public content is sourced only from allowlisted CMS readers and remains usable
without JavaScript.

## Implemented baseline

- Laravel 13 / PHP 8.3+ application skeleton
- preserved React 19/TanStack Lovable reference source under `resources/js/portfolio/`
- HTML-first localized public routes under `/fr` and `/en`
- persistent light/dark themes with separately designed desktop and mobile layouts
- supplied portrait and theme-specific logo assets
- restored single-administrator authentication and session-revocation boundary
- explicit `Profile`, `Settings` and `ActivityLog` modules with module-owned migrations
- localized profile content for FR/EN plus non-public Arabic readiness records
- encrypted private contact/social values and private-by-default visibility controls
- private verified CV uploads with integrity-checked, publication-gated public delivery
- redacted audit events and deterministic public-read cache invalidation
- Vue 3/Inertia private CMS forms isolated from the public React application
- explicit `Career` and `Skills` modules with module-owned reversible migrations
- localized experiences, verified achievements, education, certifications and spoken languages
- ordered skill categories and skills with independent publication and visibility controls
- draft-only, idempotent import of verified `docs/content/` facts without invented English copy
- typed FR/EN public readers with no locale fallback or unverified metric exposure
- keyboard-operable dashboard ordering, validation, authorization, audit and cache invalidation
- explicit `Projects` ownership for project, case-study and media persistence
- stable unique slugs, lifecycle/publication state, featured ordering and dashboard filters
- encrypted private repository URLs, encrypted storage paths and encrypted client filenames
- private UUID-based upload paths with MIME, extension, size and image-decoding validation
- administrator project/media CRUD, ordering, publication and private preview
- verified bilingual publication gates and allowlisted cached public project readers
- draft-only idempotent project imports from verified repository documentation
- localized Blade landing composition in the approved section order
- published-project index and stable-slug case-study routes
- semantic navigation, landmarks, headings, skip link and visible keyboard focus
- responsive left profile rail, central content column, contextual right rail and mobile bottom nav
- self-hosted Inter, Space Grotesk and Font Awesome assets with no public font CDN dependency
- responsive WebP derivatives of the supplied portrait and theme-aware logo variants
- publication-checked private media streaming with MIME-safe response headers
- privacy-gated Git activity, queued encrypted contact delivery and null analytics by default
- canonical, hreflang, Open Graph, Twitter, schema.org, sitemap and robots metadata
- localized privacy and safe public error pages
- Playwright no-JavaScript, keyboard, axe WCAG A/AA, desktop/mobile and light/dark capture coverage
- PHPUnit, Pint, Larastan level 6, ESLint, Stylelint, Vite build and Composer audit gates

## Reset evidence

- Fresh skeleton generated with `composer create-project laravel/laravel /tmp/portfolio-clean 13.*`.
- Generated Laravel package version: `laravel/laravel v13.8.0`.
- Installed framework version in `composer.lock`: `laravel/framework v13.20.0`.
- Historical app files, module code, Docker files, dashboard assets and built `dist/` assets were removed from the active tree.
- No archived application code or archived 3D assets were restored.

## Current objective

Start PORT-010 / issue #15 from the merged PORT-009 integration baseline in a fresh
`next issue` chat.

## PORT-009 verification

- Related issue: #14 is closed as completed; dependency #13 and PR #29 are closed and merged.
- Contact submissions are validated, throttled, honeypot-filtered and queued with encrypted
  payloads to the private configured recipient without an application contact-message table.
- Verified published PDFs use private UUID storage, SHA-256 integrity checks and locale/publication
  gates; replacement, deletion and single-published-version behavior are covered.
- Public SEO includes canonical/hreflang, Open Graph/Twitter, schema.org, sitemap and robots output.
- Public privacy defaults include a null analytics implementation, hidden activity by default,
  localized privacy content and safe 404/500 templates.
- Targeted PHPUnit, Pint, Larastan, ESLint, Stylelint, Vite, SQLite rollback/re-migrate and all four
  Playwright/no-JavaScript/axe flows pass locally.
- PR #31 is squash-merged as `dd8df49` into `release/v3.0.0/main`.
- Final Quality run #41 passes PostgreSQL migrate/rollback/re-migrate, PHPUnit, Pint, Larastan,
  ESLint, Stylelint, the production build and all Playwright/axe flows.
- The workstation's pre-merge full PHPUnit run had existing GD and audit-order limitations; the
  green Quality run #41 exercised the full PostgreSQL/backend, frontend and browser gates.

## PORT-008 verification

- Related issue: #13.
- Dependency: PORT-007 / issue #12 squash-merged through PR #28 and closed.
- Targeted PHPUnit coverage added for HTML-first output, localized stable routes, draft and
  incomplete-translation exclusion, unknown project 404s and re-authorized media delivery.
- Playwright coverage added for JavaScript-disabled navigation, keyboard focus, axe WCAG A/AA
  audits and light/dark desktop plus mobile evidence captures.
- PR #29 is squash-merged as `0a892b9`; issue #13 is closed as completed.
- Final Quality run #37 passes PostgreSQL migrate/rollback/re-migrate, PHPUnit with 403 assertions,
  Pint, Larastan, ESLint, Stylelint and the production Vite build.
- All three Playwright flows pass, including JavaScript-disabled navigation, keyboard order and
  axe WCAG A/AA validation.
- Six visually reviewed implementation captures are committed under
  `docs/captures/implemented/`.
- PORT-009 / issue #14 subsequently merged through PR #31 and is closed as completed.
- Next queue item: issue #15, PORT-010 Three.js configuration and placeholder, is ready for a fresh
  `next issue` chat.

## Autonomous defaults

- Visual choices use ADR-007 dark-first professional defaults.
- Privacy choices use ADR-007 conservative public-data defaults.
- PORT-011 may use original procedural/licensed low-poly assets and a generic stylized developer avatar when personal likeness references are unavailable.
- Codex stops only for a hard blocker defined by ADR-007.

## Source of truth

| Information | Location |
|---|---|
| Product scope | `docs/product/` |
| Architecture decisions | `docs/architecture/decisions/` |
| Autonomous delivery decision | `docs/architecture/decisions/ADR-007-autonomous-continuous-delivery.md` |
| Current status | `PROJECT_STATUS.md` |
| Ordered execution | `docs/tracking/execution-queue.md` and issue #17 |
| Work scope | GitHub Issues |
| Implementation evidence | Pull Requests |
| Agent rules | `AGENTS.md` |
| Multi-agent workflow | `docs/agents/` |
| Communication | `docs/coordination/` |
| 3D handoff | `docs/coordination/blender-handoff.md` |

## Immediate next steps

1. Start PORT-010 / issue #15 from `release/v3.0.0/main`.
2. Create `feature/PORT-010-threejs-config` in a fresh `next issue` chat.
3. Keep PORT-011 blocked until PORT-010 is merged and closed.

## Update rule

The coordinator updates this file when an issue changes state or a material architectural decision
lands. Each `next issue` chat ends after one issue's handoff or merge.
