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
3. `docs/tracking/execution-queue.md`
4. `docs/agents/sequential-feature-delivery.md`
5. relevant ADRs in `docs/architecture/decisions/`
6. relevant product documentation
7. `docs/agents/multi-agent-workflow.md`
8. `docs/coordination/communication-protocol.md`

GitHub Issues define scope and acceptance criteria. Pull Requests contain implementation evidence. Chat messages are not the durable source of truth.

## Sequential PORT delivery

The initial ordered delivery sequence is controlled by GitHub issue `#17` and `docs/tracking/execution-queue.md`.

Rules:

1. Deliver one integrated PORT issue at a time.
2. Select only the first open issue whose dependency is closed as completed and merged into `main`.
3. Read the full issue body, comments, branch name, exclusions, acceptance criteria, agents and merge contract.
4. Use the exact branch named in the issue.
5. One issue equals one branch and one pull request.
6. Use the `feature-delivery` skill for all non-trivial PORT issues.
7. Update `PROJECT_STATUS.md` and the execution queue inside the feature PR.
8. The PR body must contain `Closes #<issue-number>`.
9. After merge, verify issue closure, pull updated `main`, update controller issue `#17`, remove completed worktrees and start a new focused task/thread for the next executable issue.
10. Do not skip an issue, silently broaden scope or invent the next feature.
11. After PORT-011, stop and create release planning. Do not automatically invent PORT-012.

Issue execution markers:

- `READY_AFTER: #N`: executable after issue `#N` is completed and merged.
- `HUMAN_GATE_AFTER: #N`: a draft PR may be prepared after `#N`, but merge and continuation require explicit product-owner approval.

Read `docs/agents/sequential-feature-delivery.md` for exact start, publication, merge, closure and continuation commands.

## Automated merge boundary

The orchestrator may squash-merge and continue only when:

- all acceptance criteria are satisfied;
- every required CI and local quality gate is green;
- no unresolved QA or review finding remains;
- the branch is current with `main`;
- no secrets or unrelated changes exist;
- migration rollback is verified where applicable;
- the issue merge contract permits automatic merge;
- every required human gate is explicitly approved.

Never bypass branch protection or required reviews.

Mandatory stop conditions include:

- failing tests or checks;
- security, privacy or data-loss ambiguity;
- destructive or unverified migrations;
- production credentials or production deployment;
- conflict with an accepted ADR;
- missing visual approval;
- missing Blender asset approval, licensing or provenance;
- changed `main` that invalidates the feature;
- requirements that would need to be invented.

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
- feature branches: one issue per branch, using the exact name recorded in the issue and execution queue.

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
- documentation, `PROJECT_STATUS.md` and the execution queue updated;
- no secrets committed;
- no unrelated files changed;
- PR contains exact commands, results, risks and unresolved items;
- PR is merged according to the issue contract;
- issue closure is verified;
- controller issue `#17` is updated;
- the next issue is started only when its dependency and gates are satisfied.
