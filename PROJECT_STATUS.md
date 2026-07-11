# Project Status

## Current version

- Product version: v2
- Repository: `Alanarex/portfolio`
- Stable historical version: `release/v1.0.0`
- Active integration branch: `main`
- Development source prepared from: `release/v2.0.0/main`

## Current phase

**PHASE 0 — AUDIT AND FOUNDATION PREPARATION**

The product vision, architecture, content model, low-poly Three.js preview, quality strategy and controlled multi-agent workflow are documented. Scaffold decisions are accepted.

## Approved technical baseline

- Laravel 13 / PHP 8.3+
- PostgreSQL
- Redis
- Blade SSR public portfolio
- Vue 3 + TypeScript + Inertia dashboard
- vanilla Three.js + TypeScript preview/fullscreen module
- npm
- PHPUnit
- one private administrator initially
- Docker Compose locally
- OVH VPS + Docker for production

See `docs/architecture/decisions/ADR-005-scaffold-baseline.md`.

## Current objective

Audit the existing React/TypeScript v2 prototype, then build a verified Laravel 13 modular foundation without losing useful frontend or 3D work.

## Source of truth

| Information | Authoritative location |
|---|---|
| Product scope | `docs/product/` |
| Architecture decisions | `docs/architecture/decisions/` |
| Current status | `PROJECT_STATUS.md` |
| Work items | GitHub Issues |
| Implementation evidence | Pull Requests |
| Agent rules | `AGENTS.md` |
| Multi-agent workflow | `docs/agents/` |
| Human/tool communication | `docs/coordination/` |
| 3D asset contract | `docs/coordination/blender-handoff.md` |

## Immediate next steps

1. Complete PORT-000: audit the existing React/TypeScript/Three.js prototype.
2. Start PORT-002: scaffold Laravel 13 after the audit identifies retained/migrated prototype assets.
3. Add Docker services: Nginx, PostgreSQL, Redis, queue worker, scheduler and Mailpit.
4. Configure Pint, Larastan/PHPStan, PHPUnit, ESLint, Stylelint, build checks and CI.
5. Implement the single-admin private authentication boundary.
6. Implement the public portfolio CMS and HTML landing page.
7. Integrate a temporary low-poly Three.js scene before final Blender assets.

## Non-blocking parallel work

- Blender/avatar reference preparation;
- design references and captures;
- deep analysis of featured project repositories;
- confirmation of public contact/CV/activity visibility.

## Update rule

The orchestrator updates this file after every merged milestone or material architectural decision.
