# Project Status

## Version and branches

- Product version: v3
- Integration branch: `release/v3.0.0/main`
- Current feature: PORT-007 projects, case studies and media (issue #12)
- Feature branch: `feature/PORT-007-projects-media`
- Delivery mode: gated autonomous delivery
- Historical original: `release/v1.0.0`
- Archived React prototype: `archive/react-v2-prototype-2026-07-11`

## Current phase

**PORT-007 IMPLEMENTED — SECURITY REVIEW CLEAR, CI PENDING**

Laravel now owns structured projects, localized case studies and private media behind authenticated
dashboard editors. Public readers expose only published, locale-complete, verified and explicitly
visible data; the public React/TanStack interface remains visually unchanged until PORT-008.

## Implemented baseline

- Laravel 13 / PHP 8.3+ application skeleton
- React 19 and TanStack Router public frontend mounted through Laravel Vite
- light/dark themes, French/English preferences and four audience modes
- supplied portrait and theme-specific logo assets
- restored single-administrator authentication and session-revocation boundary
- explicit `Profile`, `Settings` and `ActivityLog` modules with module-owned migrations
- localized profile content for FR/EN plus non-public Arabic readiness records
- encrypted private contact/social values and private-by-default visibility controls
- metadata-only CV versions with no upload or public download route
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
- PHPUnit, Pint, Larastan level 6, ESLint, Stylelint, Vite build and Composer audit gates

## Reset evidence

- Fresh skeleton generated with `composer create-project laravel/laravel /tmp/portfolio-clean 13.*`.
- Generated Laravel package version: `laravel/laravel v13.8.0`.
- Installed framework version in `composer.lock`: `laravel/framework v13.20.0`.
- Historical app files, module code, Docker files, dashboard assets and built `dist/` assets were removed from the active tree.
- No archived application code or archived 3D assets were restored.

## Current objective

Publish the draft pull request and complete GitHub CI for issue #12.

## PORT-007 verification

- Related issue: #12.
- Targeted coverage added for authorization, stable slugs, publication/locale/verification gates,
  public/private repository rules, encrypted metadata, upload spoofing/size rules, storage cleanup,
  public DTO allowlists, cache invalidation, audit redaction and idempotent seeds.
- Local frontend checks: ESLint, Stylelint and Vite production build pass.
- Local PHP syntax: every PHP file parses successfully with an independent parser.
- Environment limitation: this Work Mode container has no PHP, Composer or Docker. PHPUnit, Pint,
  Larastan, Composer audit and migrate/rollback/re-migrate must run in GitHub Actions.
- QA: the routed read-only review found no remaining blocking, high or medium issue after
  retry-safe deletion, upload/deletion locking, bounded full-image decoding and reactive media-form
  remediations.
- Remaining blocker: PHP quality gates and PostgreSQL migration rollback evidence are pending in CI.
- Next queue item after merge: issue #13, PORT-008 public portfolio and case studies, in a fresh
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

1. Open the PORT-007 draft pull request and verify PostgreSQL migrate/rollback/re-migrate, PHPUnit,
   Pint, Larastan, Composer audit and frontend CI.
2. Hand off issue #12 only after exact evidence and remaining risks are recorded.
3. A fresh `next issue` chat may select issue #13 only after PORT-007 is merged and closed.

## Update rule

The coordinator updates this file when an issue changes state or a material architectural decision
lands. Each `next issue` chat ends after one issue's handoff or merge.
