# Portfolio Platform v2

Portfolio Platform v2 is being rebuilt from zero as a Laravel 13 modular monolith.

## Repository state

The `main` branch intentionally contains only:

- product and architecture documentation;
- Codex and multi-agent configuration;
- GitHub issue/PR templates;
- coordination and readiness files.

No legacy application code is reused automatically.

Historical versions remain available on:

- `release/v1.0.0` — original portfolio;
- `archive/react-v2-prototype-2026-07-11` — archived React/TypeScript/Three.js prototype and repository state before the clean start.

## Approved stack

- Laravel 13 / PHP 8.3+
- PostgreSQL
- Redis
- Blade SSR
- Vue 3 + TypeScript + Inertia
- vanilla Three.js + TypeScript
- npm
- PHPUnit
- Docker Compose
- OVH VPS + Docker

## Next milestone

`PORT-002` — scaffold the clean Laravel 13 modular foundation.

Read before contributing:

- `AGENTS.md`
- `PROJECT_STATUS.md`
- `docs/architecture/decisions/ADR-005-scaffold-baseline.md`
- `docs/architecture/decisions/ADR-006-clean-v2-start.md`
- `docs/agents/multi-agent-workflow.md`
- `docs/readiness/pre-development-checklist.md`
