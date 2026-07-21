# Project Status

## Version and branches

- Product version: v3
- Integration branch: `release/v3.0.0/main`
- Current feature: none
- Feature branch: none
- Delivery mode: manual reset requested by the repository owner
- Historical original: `release/v1.0.0`
- Archived React prototype: `archive/react-v2-prototype-2026-07-11`

## Current phase

**V3 READY FOR SCOPED DEVELOPMENT**

The active application code has been reset to a fresh `laravel/laravel` 13.x skeleton while
preserving Git history, `AGENTS.md`, `.agents/`, `.codex/`, `docs/`, `README.md` and this
status file.

## Implemented baseline

- Laravel 13 / PHP 8.3+ application skeleton
- default Laravel welcome page and routes
- default migrations, factories and seeders
- Vite frontend entrypoint
- PHPUnit example tests

## Reset evidence

- Fresh skeleton generated with `composer create-project laravel/laravel /tmp/portfolio-clean 13.*`.
- Generated Laravel package version: `laravel/laravel v13.8.0`.
- Installed framework version in `composer.lock`: `laravel/framework v13.20.0`.
- Historical app files, module code, Docker files, dashboard assets and built `dist/` assets were removed from the active tree.
- No archived application code or archived 3D assets were restored.

## Current objective

Use the clean Laravel skeleton to port the Lovable V3 interaction model feature-by-feature.
The normalized product specification, prototype provenance, capture matrix, supplied brand
assets, and development handoff now live in the repository.

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

1. Reconcile the old PORT queue against `docs/product/v3-social-portfolio-spec.md`.
2. Start the V3 UI foundation from `docs/readiness/v3-development-handoff.md`.
3. Capture every implemented public route in FR/EN, light/dark, desktop/mobile.
4. Reintroduce infrastructure, modules and dashboard features through explicit issue scope.

## Update rule

The orchestrator updates this file after each merged milestone or material architectural decision and continues to the next executable issue unless an ADR-007 hard blocker exists.
