# Portfolio Platform v2

Portfolio Platform v2 is a clean Laravel 13 modular monolith.

## Repository state

PORT-002 introduces the foundation without reusing legacy application code. The application
provides a server-rendered public page, an authenticated Inertia/Vue administrator shell,
and an optional lazy-loaded Three.js placeholder.

Historical versions remain available on their release/archive branches for reference only.
They are not a source for this scaffold.

## Approved stack

- Laravel 13 / PHP 8.3+
- PostgreSQL and Redis
- Blade SSR
- Vue 3 + TypeScript + Inertia
- vanilla Three.js + TypeScript
- npm and Vite
- PHPUnit, Pint and Larastan
- Docker Compose, Nginx and Mailpit
- OVH VPS + Docker as the future production target

## Local setup

Requirements: Docker Desktop with Linux containers and Docker Compose.

```bash
cp .env.example .env
docker compose build app
docker compose up -d --wait
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan admin:provision
```

The public application is available at `http://localhost:8080`, Mailpit at
`http://localhost:8025`, liveness at `/up`, and PostgreSQL/Redis readiness at `/ready`.
The administrator command prompts for credentials and no default password is committed.
Public registration is disabled.

## Architecture conventions

- `app/Core/` contains application-wide infrastructure.
- `Modules/*/module.json` declares enabled module providers; discovery is deterministic and validated.
- Each module provider owns its routes, views, migrations, configuration and commands.
- Public content remains Blade SSR; Inertia/Vue is restricted to the authenticated dashboard.
- `resources/js/three-preview/` is a separate vanilla Three.js entry loaded dynamically with a static HTML/CSS fallback.

## Quality checks

```bash
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse --no-progress
docker compose exec app npm run lint
docker compose exec app npm run stylelint
docker compose exec app npm run build
docker compose config --quiet
```

GitHub Actions repeats the backend, frontend and container gates. Production deployment is
not part of PORT-002.

## Current milestone

`PORT-002` — Laravel 13 modular foundation implemented and under review in GitHub issue #6.

Read before contributing:

- `AGENTS.md`
- `PROJECT_STATUS.md`
- `docs/architecture/decisions/ADR-005-scaffold-baseline.md`
- `docs/architecture/decisions/ADR-006-clean-v2-start.md`
- `docs/agents/multi-agent-workflow.md`
- `docs/readiness/pre-development-checklist.md`
