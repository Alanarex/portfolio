# AGENTS.md

## Mission

Build Portfolio Platform v2 as a clean Laravel 13 modular monolith. The repository intentionally starts with documentation and agent configuration only. Historical implementations are preserved on archive/release branches and must not be copied into the new application unless a future issue explicitly requests it.

## Approved stack

- PHP 8.3+
- Laravel 13
- PostgreSQL
- Redis
- Blade SSR for public pages
- Vue 3 + TypeScript + Inertia for the private dashboard
- vanilla Three.js + TypeScript for the isolated low-poly 3D preview/fullscreen module
- Vite and npm
- PHPUnit
- Pint
- Larastan/PHPStan
- ESLint and Stylelint
- Docker Compose
- Nginx
- Mailpit locally
- OVH VPS + Docker as production target

## Source of truth

Before non-trivial work, read:

1. `PROJECT_STATUS.md`
2. the linked GitHub Issue
3. relevant ADRs in `docs/architecture/decisions/`
4. relevant product documentation
5. `docs/agents/multi-agent-workflow.md`
6. `docs/coordination/communication-protocol.md`

GitHub Issues define scope and acceptance criteria. Pull Requests contain implementation evidence. Chat messages are not the durable source of truth.

## Clean-start rule

- `main` is the clean v2 integration branch.
- Do not restore React prototype files, old static portfolio files, GLB assets or legacy dependencies from archive branches unless an issue explicitly authorizes it.
- Historical code is reference-only.
- PORT-002 scaffolds a new Laravel application from zero.
- Preserve all current documentation, `.codex/`, `.agents/`, `.github/`, `README.md` and `PROJECT_STATUS.md` during scaffolding.

## Branches

- `main`: clean v2 integration branch.
- `release/v1.0.0`: historical original portfolio.
- `archive/react-v2-prototype-2026-07-11`: archived React/TypeScript/Three.js prototype and pre-clean repository state.
- feature branches: one issue per branch.

Never commit application work directly to `main`.

## Modular structure

Target structure:

```text
app/Core/
Modules/<ModuleName>/
├── Application/
├── Domain/
├── Infrastructure/
├── Http/
├── Models/
├── Providers/
├── Database/
├── Routes/
├── Resources/
├── Tests/
├── Config/
├── module.json
└── README.md
```

Do not create empty directories only to imitate the template.

A module owns its business rules, migrations, routes, policies, requests, resources, tests, frontend assets and documentation.

Cross-module communication order:

1. contracts/interfaces;
2. application services;
3. domain/application events;
4. dedicated read models or queries.

Avoid circular dependencies.

## Backend rules

Controllers must remain thin. Preferred flow:

```text
Request → FormRequest → Policy → Action/Query → Repository/Integration → Resource/Response
```

Use typed PHP, DTOs, Value Objects and Enums where appropriate. Do not put business logic in controllers, views, jobs or provider classes.

Before changing persistence, evaluate indexes, foreign keys, nullability, transactions, rollback and PostgreSQL-specific behavior.

Avoid N+1 queries and uncontrolled array contracts.

## Security

For every endpoint evaluate:

- authentication;
- authorization;
- validation;
- rate limiting;
- CSRF;
- mass assignment;
- IDOR;
- sensitive data exposure;
- upload safety.

Never commit or log passwords, tokens, secrets, private keys, session cookies or sensitive payloads.

The initial dashboard has one private administrator, but authorization boundaries must allow later RBAC without pretending that multi-user RBAC already exists.

## Integrations and queues

External APIs must be called through integration clients, DTOs, Actions and queued jobs where appropriate. Never call GitHub, GitLab, deployment or licensing APIs directly from controllers or views.

Jobs should be idempotent when possible.

## Frontend rules

Public portfolio:

- server-rendered and HTML-first;
- semantic, indexable and accessible;
- critical content never depends on JavaScript or WebGL.

Dashboard:

- Vue 3 + TypeScript + Inertia;
- shared design tokens;
- keyboard-accessible components;
- responsive layouts;
- explicit loading, empty and error states.

Three.js:

- optional, lazy-loaded low-poly preview;
- click opens fullscreen;
- poster, mobile, reduced-motion and WebGL-failure fallbacks required;
- pause rendering outside the viewport;
- clamp device pixel ratio;
- avoid heavy post-processing, physics, dynamic hair and cloth in the first release;
- every hotspot has an HTML equivalent;
- follow `docs/product/three-d-preview.md` and the Blender handoff contract.

## Testing and quality

Do not weaken or disable quality gates to make work pass.

Required where applicable:

- targeted PHPUnit tests;
- full relevant PHPUnit suite;
- Pint check;
- Larastan/PHPStan;
- ESLint;
- Stylelint;
- production frontend build;
- Playwright flows for critical UI;
- accessibility checks;
- 3D payload and runtime performance checks;
- migration rollback verification.

Inspect actual scripts before assuming command names.

## Multi-agent rules

- One orchestrator owns architecture, task decomposition, integration and final validation.
- Maximum six total threads.
- Maximum nesting depth one.
- Maximum two concurrent write agents initially.
- Use separate Git worktrees for concurrent writers.
- One explicit owner per path.
- Never let two write agents edit shared root manifests, migrations, routes or frontend entry points simultaneously.
- Subagents do not merge their own work.
- Use the handoff format in `docs/agents/task-handoff.md`.

Parallelize exploration, architecture review, security review, test-gap analysis and independent modules. Serialize shared write-heavy work.

## Documentation and ADRs

Update documentation when behavior, setup, module boundaries, APIs, deployment or current project state changes.

Create or update an ADR for significant decisions affecting architecture, persistence, authentication, public APIs, module boundaries, deployment, infrastructure or major dependencies.

## Definition of done

A task is complete only when applicable criteria are satisfied:

- issue acceptance criteria implemented;
- authorization and validation implemented;
- tests added and passing;
- static analysis and formatting passing;
- frontend lint/build passing;
- accessibility/performance validated where relevant;
- migration rollback checked;
- documentation and `PROJECT_STATUS.md` updated;
- no secrets committed;
- no unrelated files changed;
- PR contains exact commands, results, risks and unresolved items.
