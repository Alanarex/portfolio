# Project Status

## Current version

- Product version: v2
- Repository: `Alanarex/portfolio`
- Stable historical version: `release/v1.0.0`
- Active integration branch: `main`
- Development source prepared from: `release/v2.0.0/main`

## Current phase

**PRE-DEVELOPMENT / PHASE 0**

The product vision, architecture, content model, low-poly Three.js preview, quality strategy and controlled multi-agent workflow are documented.

## Current objective

Prepare a verified Laravel 13 modular foundation without losing the existing React/TypeScript v2 prototype.

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

1. Audit the existing v2 React/TypeScript prototype.
2. Decide which prototype assets and interactions are retained.
3. Scaffold the Laravel 13 modular application.
4. Add Docker, MySQL, Redis and Nginx local services.
5. Configure quality gates and CI.
6. Implement private dashboard authentication.
7. Implement the public portfolio CMS before the final Three.js room.

## Blocking decisions

See `docs/readiness/open-decisions.md`.

## Update rule

The orchestrator updates this file after every merged milestone or material architectural decision.
