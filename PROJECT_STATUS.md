# Project Status

## Version and branches

- Product version: v3
- Integration branch: `release/v3.0.0/main`
- Current feature: Lovable React frontend integration (PR #23)
- Feature branch: `feature/port-frontend-lovable-import`
- Delivery mode: gated autonomous delivery
- Historical original: `release/v1.0.0`
- Archived React prototype: `archive/react-v2-prototype-2026-07-11`

## Current phase

**V3 LOVABLE FRONTEND INTEGRATED — PR #23 READY TO MERGE**

Laravel remains the backend and HTTP entry point. The Lovable React/TanStack interface is now
mounted through Laravel Vite and retains its routes, audience modes, themes, navigation and
animations.

## Implemented baseline

- Laravel 13 / PHP 8.3+ application skeleton
- React 19 and TanStack Router public frontend mounted through Laravel Vite
- light/dark themes, French/English preferences and four audience modes
- supplied portrait and theme-specific logo assets
- ESLint, Stylelint, Vite build, PHPUnit and Pint CI checks

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

1. Merge PR #23 after all required checks pass.
2. Start issue #10 (PORT-005 profile and settings CMS).
3. Replace visual fixture data through typed Laravel APIs incrementally.
4. Reintroduce Docker and static analysis through explicit, configured issue scope.

## Update rule

The orchestrator updates this file after each merged milestone or material architectural decision and continues to the next executable issue unless an ADR-007 hard blocker exists.
