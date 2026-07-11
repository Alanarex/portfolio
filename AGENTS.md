# AGENTS.md

## Mission

Build Portfolio Platform v2 as a clean Laravel 13 modular monolith. Historical implementations remain reference-only on archive/release branches and must not be copied into the active application unless a future issue explicitly authorizes it.

## Approved stack

- PHP 8.3+
- Laravel 13
- PostgreSQL
- Redis
- Blade SSR for public pages
- Vue 3 + TypeScript + Inertia for the private dashboard
- vanilla Three.js + TypeScript for the isolated low-poly 3D module
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
2. the linked GitHub issue
3. `docs/tracking/execution-queue.md`
4. `docs/agents/sequential-feature-delivery.md`
5. `docs/architecture/decisions/ADR-007-autonomous-continuous-delivery.md`
6. relevant ADRs in `docs/architecture/decisions/`
7. relevant product/module documentation
8. `docs/agents/multi-agent-workflow.md`
9. `docs/coordination/communication-protocol.md`

GitHub Issues define scope and acceptance criteria. Pull Requests contain implementation evidence. Chat messages are not the durable source of truth.

## Autonomous continuous PORT delivery

The ordered sequence is controlled by GitHub issue `#17` and `docs/tracking/execution-queue.md`.

Rules:

1. Deliver one integrated PORT issue at a time.
2. Select the first open issue whose dependency is completed and merged into `main`.
3. Read the full issue, comments, exact branch, exclusions, acceptance criteria, suggested agents and merge contract.
4. Use the exact branch recorded in the issue and queue.
5. One issue equals one branch and one pull request.
6. Use the `feature-delivery` skill for all non-trivial PORT issues.
7. Update `PROJECT_STATUS.md` and the execution queue in each feature PR.
8. The PR body must contain `Closes #<issue-number>`.
9. After merge, verify issue closure, update controller issue `#17`, clean worktrees, pull `main` and immediately start the next executable issue.
10. Do not wait for a new prompt or a new chat between PORT issues.
11. Do not skip issues, silently broaden scope or invent new product requirements.
12. Use ADR-007 defaults for ordinary visual, privacy and v1 3D decisions.
13. After PORT-011, close the controller and create a release-readiness issue; do not invent PORT-012 automatically.

## Automated merge boundary

The orchestrator may squash-merge and continue only when:

- every acceptance criterion is satisfied;
- all required local and GitHub Actions checks are green;
- no unresolved QA or review finding remains;
- the branch is current with `main`;
- no secrets, private source code or unrelated changes exist;
- migration rollback is verified where applicable;
- the issue/ADR permits the selected fallback;
- branch protection and required reviews are respected.

Never bypass branch protection or required reviews.

## Autonomous defaults

Unless an issue explicitly overrides them, use ADR-007:

### Visual

- dark-first professional UI;
- neutral navy/slate surfaces with one restrained warm accent;
- accessible system-first typography or an openly licensed bundled font;
- responsive layouts, visible focus and reduced motion;
- generate screenshots and let QA select the strongest compliant direction.

### Privacy

- exact address and phone hidden;
- contact form as the public contact channel;
- public email only when explicitly configured;
- no analytics vendor by default;
- no private GitHub/GitLab activity exposed;
- CV download only for a verified published file;
- minimal or no contact-message persistence.

### 3D / Blender

- never restore archived GLB files;
- prefer original procedural low-poly assets generated through code or Blender scripting;
- document provenance for every external asset;
- when personal likeness references are unavailable, ship v1 with a generic stylized developer avatar that follows the required node/animation contract;
- personalized likeness becomes a later asset revision, not a blocker;
- no audio, heavy post-processing, physics, dynamic hair or cloth.

## Hard stop conditions

Stop only when a blocker cannot be safely resolved after reasonable diagnosis and repair attempts:

- failing tests or CI;
- unresolved security, privacy, licensing or data-loss risk;
- destructive migration without verified rollback;
- production credentials or production deployment approval;
- GitHub permissions or branch protection preventing publication/merge;
- conflict with an accepted ADR that requires product-level reversal;
- unavailable external asset/tooling that cannot be replaced by an allowed procedural or licensed fallback;
- changed `main` invalidating the implementation.

## Clean-start rule

- `main` is the v2 integration branch.
- Do not restore React prototype files, old static portfolio files, archived GLB assets or legacy dependencies unless an issue explicitly authorizes it.
- Historical code is reference-only.
- Preserve documentation, `.codex/`, `.agents/`, `.github/`, `README.md` and `PROJECT_STATUS.md`.

## Branches

- `main`: v2 integration branch.
- `release/v1.0.0`: historical original portfolio.
- `archive/react-v2-prototype-2026-07-11`: archived React/TypeScript/Three.js prototype.
- feature branches: one issue per branch, using the exact recorded name.

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

Controllers remain thin:

```text
Request → FormRequest → Policy → Action/Query → Repository/Integration → Resource/Response
```

Use typed PHP, DTOs, Value Objects and Enums where appropriate. Do not put business logic in controllers, views, jobs or provider classes.

Before changing persistence, evaluate indexes, foreign keys, nullability, transactions, rollback and PostgreSQL behavior. Avoid N+1 queries and uncontrolled array contracts.

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

The initial dashboard has one private administrator, but authorization boundaries must allow later RBAC without pretending multi-user RBAC already exists.

## Integrations and queues

External APIs must use integration clients, DTOs, Actions and queued jobs where appropriate. Never call GitHub, GitLab, deployment or licensing APIs directly from controllers or views. Jobs should be idempotent when possible.

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

- optional and lazy-loaded;
- inline preview plus fullscreen interaction;
- poster, mobile, reduced-motion and WebGL-failure fallbacks;
- pause rendering outside viewport;
- clamp DPR;
- avoid heavy post-processing, physics, dynamic hair and cloth;
- every hotspot has an HTML equivalent;
- follow `docs/product/three-d-preview.md` and the Blender handoff contract.

## Testing and quality

Do not weaken or disable quality gates.

Required where applicable:

- targeted and relevant full PHPUnit suites;
- Pint;
- Larastan/PHPStan;
- ESLint;
- Stylelint;
- production frontend build;
- Playwright critical flows;
- accessibility checks;
- 3D payload/runtime checks;
- migration rollback verification.

Inspect actual scripts before assuming command names.

## Multi-agent rules

- One orchestrator owns architecture, decomposition, integration and final validation.
- Maximum six total threads.
- Maximum nesting depth one.
- Maximum two concurrent write agents.
- Use separate Git worktrees for concurrent writers.
- One explicit owner per path.
- Never let two write agents edit shared root manifests, migrations, routes or frontend entry points simultaneously.
- Subagents do not merge their own work.
- Use `docs/agents/task-handoff.md`.

Parallelize exploration, architecture review, security review, test-gap analysis and independent modules. Serialize shared write-heavy work.

## Documentation and ADRs

Update documentation when behavior, setup, module boundaries, APIs, deployment or current project state changes.

Create or update an ADR for significant decisions affecting architecture, persistence, authentication, public APIs, module boundaries, deployment, infrastructure or major dependencies.

## Definition of done

A feature is complete only when applicable criteria are satisfied:

- issue acceptance criteria implemented;
- authorization and validation implemented;
- tests added and passing;
- static analysis and formatting passing;
- frontend lint/build passing;
- accessibility/performance validated;
- migration rollback checked;
- documentation, `PROJECT_STATUS.md` and execution queue updated;
- no secrets or unrelated files;
- PR contains commands, results, risks and evidence;
- PR merged according to the contract;
- issue closure verified;
- controller issue `#17` updated;
- next executable issue started immediately unless a hard blocker exists.
