# Project Status

## Version and branches

- Product version: v2
- Integration branch: `main`
- Historical original: `release/v1.0.0`
- Archived React prototype: `archive/react-v2-prototype-2026-07-11`

## Current phase

**PHASE 0 — CLEAN FOUNDATION READY FOR SCAFFOLDING**

The repository has been cleaned. Legacy application files, React dependencies and temporary 3D assets are no longer present on `main`.

## Approved baseline

- Laravel 13 / PHP 8.3+
- PostgreSQL
- Redis
- Blade SSR public portfolio
- Vue 3 + TypeScript + Inertia private dashboard
- vanilla Three.js + TypeScript preview/fullscreen module
- npm
- PHPUnit
- single private administrator initially
- Docker Compose locally
- OVH VPS + Docker in production

## Current objective

Complete `PORT-002`: scaffold and validate the new Laravel 13 modular foundation from zero while preserving the documentation and agent configuration already present.

## Source of truth

| Information | Location |
|---|---|
| Product scope | `docs/product/` |
| Architecture decisions | `docs/architecture/decisions/` |
| Current status | `PROJECT_STATUS.md` |
| Work scope | GitHub Issues |
| Implementation evidence | Pull Requests |
| Agent rules | `AGENTS.md` |
| Multi-agent workflow | `docs/agents/` |
| Communication | `docs/coordination/` |
| 3D handoff | `docs/coordination/blender-handoff.md` |

## Immediate next steps

1. Start PORT-002 on a dedicated branch from clean `main`.
2. Scaffold Laravel 13 without deleting documentation or agent files.
3. Configure Docker, PostgreSQL, Redis, Nginx, workers, scheduler and Mailpit.
4. Configure Blade, Vue/Inertia and an isolated Three.js placeholder entry.
5. Configure tests, analysis, linting, build and GitHub Actions.
6. Open a draft PR with reproducible evidence.

## Parallel non-blocking work

- Blender/avatar references;
- low-poly room blockout;
- design system and mockups;
- featured-project repository analysis;
- public CV/contact/activity visibility decisions.

## Update rule

The orchestrator updates this file after each merged milestone or material architectural decision.
