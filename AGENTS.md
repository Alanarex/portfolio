# AGENTS.md

## Project Overview

This repository contains a modular Laravel platform composed of:

1. Public portfolio.
2. Private administration dashboard.
3. Project and feature tracking.
4. Demo management.
5. GitHub and GitLab integrations.
6. Deployment management.
7. Reusable Laravel module registry.
8. Licensing services.
9. Monitoring, analytics and audit logs.
10. Future mobile API.

The application is a modular monolith.

## Core stack

Backend:
- PHP 8.3+
- Laravel 13
- MySQL/MariaDB
- Redis
- queues
- scheduler

Frontend:
- JavaScript or TypeScript according to repository configuration
- Vite
- SCSS
- Bootstrap where configured
- Three.js / React Three Fiber only where applicable

Infrastructure:
- Docker
- Docker Compose
- Nginx
- GitHub Actions and/or GitLab CI
- local, staging and production environments

Quality:
- Pint
- PHPStan/Larastan
- PHPUnit or Pest
- ESLint
- Stylelint
- coverage

## Before editing

1. Read this file.
2. Read the relevant module README.
3. Inspect nearby code.
4. Read the feature tracking document when available.
5. Inspect related tests.
6. Follow existing conventions.

## General rules

Do not:
- modify unrelated files;
- perform broad refactors without requirement;
- silently change public APIs;
- silently modify database schemas;
- remove tests to make the suite pass;
- weaken static analysis;
- reduce coverage thresholds;
- disable linting rules without documented justification;
- put secrets in source code;
- expose private dashboard routes;
- put business logic in controllers;
- call external APIs directly from views.

Prefer small focused changes.

## Module structure

```text
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

Do not create empty directories only to satisfy the structure.

## Module boundaries

A module owns:
- business rules;
- migrations;
- models;
- routes;
- requests;
- policies;
- API resources;
- tests;
- module frontend assets;
- documentation.

Cross-module communication order of preference:

1. Contracts/interfaces.
2. Application services.
3. Domain/application events.
4. Dedicated read models or queries.

Avoid circular dependencies.

## Controller rules

Controllers may:
- receive validated input;
- authorize;
- call an Action, Query or Application Service;
- return a response.

Controllers must not:
- contain business logic;
- construct large queries;
- perform deployment logic;
- directly call GitHub/GitLab;
- make licensing decisions.

Preferred flow:

```text
Request
→ FormRequest
→ Policy
→ Action
→ Repository/Integration
→ Resource/Response
```

## Actions

Use Actions for explicit application use cases.

Examples:
- CreateProjectAction
- PublishProjectAction
- ActivateDemoAction
- DisableDemoAction
- CreateDeploymentAction
- ValidateLicenseAction
- SyncGitHubActivityAction

Actions should:
- have one responsibility;
- use typed input;
- return typed output where practical;
- use transactions where required;
- dispatch events after successful state changes.

## Database rules

Before changing migrations:
- inspect related schema;
- preserve naming conventions;
- evaluate indexes;
- evaluate foreign keys;
- evaluate nullability;
- consider rollback.

Avoid N+1 queries.

Use eager loading where appropriate.

## API rules

Version public and mobile APIs:

```text
/api/v1/...
```

Use:
- Form Requests;
- Policies;
- API Resources;
- explicit HTTP status codes.

Do not expose Eloquent models as public API contracts.

## Licensing

Do not validate licenses remotely on every HTTP request.

Use:
- periodic validation;
- signed responses;
- local cache;
- expiration;
- grace period;
- audit;
- revocation.

## GitHub/GitLab

Do not call provider APIs from controllers or views.

Use:
- integration clients;
- DTOs;
- actions;
- jobs;
- caching;
- webhooks.

Never log API tokens.

## Queue rules

Use queues for:
- Git synchronization;
- statistics aggregation;
- health checks;
- notifications;
- deployments;
- expensive media processing;
- non-blocking integrations.

Jobs should be idempotent where possible.

## Logging

Use structured logs with relevant context:
- request_id;
- user_id;
- project_id;
- demo_id;
- deployment_id;
- environment;
- module.

Never log:
- passwords;
- tokens;
- API secrets;
- session cookies;
- sensitive request bodies.

## Tests

Tests live inside the module they cover.

```text
Modules/<Module>/Tests/
├── Unit/
├── Integration/
└── Feature/
```

Test behavior, not implementation details.

Add regression tests for confirmed bugs when reasonable.

## Quality commands

Inspect `composer.json` and `package.json` before assuming script names.

Typical commands:

```bash
php artisan test
composer analyse
composer lint
npm run lint
npm run stylelint
npm run test
npm run build
```

## PHP conventions

Prefer:
- strict types;
- typed properties;
- typed parameters;
- return types;
- DTOs;
- Value Objects;
- Enums;
- Actions;
- Policies;
- Form Requests;
- API Resources.

Avoid:
- uncontrolled arrays;
- large service classes;
- magic state strings;
- deeply nested conditionals.

## JavaScript conventions

Use:
- ES modules;
- const by default;
- async/await;
- explicit error handling.

Avoid:
- global mutable state;
- inline JavaScript;
- duplicated API clients;
- large page scripts with mixed responsibilities.

## SCSS conventions

Use:
- shared design tokens;
- module-scoped styles;
- limited nesting;
- no arbitrary repeated values;
- minimal use of `!important`.

## Security checklist

For each endpoint evaluate:
- authentication;
- authorization;
- validation;
- rate limiting;
- CSRF;
- mass assignment;
- IDOR;
- sensitive data exposure;
- upload safety.

Private files remain private by default.

## Feature tracking

Feature IDs examples:
- PORT-001
- DASH-001
- DEMO-001
- DEPLOY-001
- LIC-001
- TASK-001
- GITHUB-001
- GITLAB-001
- OBS-001

For tracked features:

1. Read feature document.
2. Verify acceptance criteria.
3. Identify affected modules.
4. Implement the smallest coherent change.
5. Add or update tests.
6. Update documentation.
7. Report satisfied acceptance criteria.
8. Report unresolved issues.

## ADR

Create or update an ADR for significant changes affecting:
- architecture;
- persistence;
- public APIs;
- module boundaries;
- deployment;
- authentication;
- licensing;
- major external dependencies;
- infrastructure topology.

ADR format:

```markdown
# ADR-XXX: Title

## Status

Proposed / Accepted / Superseded

## Context

## Decision

## Consequences

## Alternatives Considered
```

## Definition of Done

A feature is complete when applicable requirements are satisfied:

- acceptance criteria implemented;
- authorization implemented;
- validation implemented;
- tests added or updated;
- targeted tests passing;
- static analysis passing;
- Pint passing;
- frontend lint passing;
- Stylelint passing;
- build passing;
- documentation updated;
- migration rollback verified;
- API compatibility considered;
- logs added where relevant;
- audit logs added for business-sensitive actions;
- no secrets committed;
- no unrelated files changed.

## Priority principle

Optimize for:

1. Correctness.
2. Security.
3. Maintainability.
4. Testability.
5. Observability.
6. Performance.
7. Developer experience.

## UI Reference Rules

Before implementing or modifying a UI screen:

1. Read the relevant product documentation.
2. Check `docs/ui/screen-map.md`.
3. Inspect related files in `docs/captures/mockups/`.
4. Inspect `docs/captures/references/` only for design direction.
5. Do not reproduce third-party interfaces pixel-for-pixel.
6. Treat `mockups/` as project requirements.
7. Treat `references/` as inspiration only.
8. Treat `implemented/` as the current-state visual record.
9. Preserve responsive behavior and accessibility requirements.
10. Update implemented screenshots after a validated UI milestone.

## Repository Analysis Rules

When analyzing a project repository:

1. Inspect metadata and README.
2. Inspect dependency manifests and framework versions.
3. Inspect Docker and CI/CD files.
4. Inspect tests and quality configuration.
5. Separate declared, detected and manually validated technologies.
6. Never infer a technology from a repository name alone.
7. Do not expose private repository contents publicly.
8. Prefer verified metrics over estimates.

## Multi-Agent Coordination Rules

Use subagents for non-trivial work only when parallelism improves quality or speed.

- Prefer parallel read-heavy exploration, review, documentation and test analysis.
- Use separate Git worktrees for concurrent write agents.
- Never assign the same files to multiple write agents.
- Keep `agents.max_depth = 1`; subagents must not recursively spawn agents.
- Start with at most two concurrent write agents.
- The parent/orchestrator owns architecture decisions, integration and final quality gates.
- Subagents must use the handoff format in `docs/agents/task-handoff.md`.
- Do not spawn agents for trivial changes.
- Wait for all requested subagents before making integration decisions.

Read:

- `docs/agents/multi-agent-workflow.md`
- `docs/agents/agent-roles.md`
- `docs/agents/context-strategy.md`

## 3D Portfolio Rules

The portfolio is not a full 3D website.

- The Three.js room is a lazy-loaded inline preview with fullscreen expansion.
- Essential content must remain normal HTML.
- Use a low-poly stylized scene.
- Do not add React solely for Three.js.
- Do not autoplay audio.
- Respect the asset budgets and naming contract in `docs/product/three-d-preview.md`.
- Always implement poster, WebGL failure, mobile and reduced-motion fallbacks.
- Pause the render loop outside the viewport.
- Do not add heavy post-processing, physics, dynamic hair or cloth in the initial release.
- Dashboard configuration controls scene metadata and assets; it does not edit geometry.

## Coordination Source of Truth

Before starting a non-trivial task, read:

- `PROJECT_STATUS.md`;
- the linked GitHub Issue;
- relevant accepted ADRs;
- `docs/coordination/communication-protocol.md`;
- relevant product and architecture documentation.

GitHub Issues define work scope and acceptance criteria. Pull Requests contain implementation evidence. Chat messages are not the durable source of truth.

When handing work between agents or tools, use `docs/coordination/agent-handoff-template.md` or the more specific Blender contract.

The existing React/TypeScript v2 prototype must be audited before removal. Classify files as retain, adapt, migrate, archive or remove and record evidence.
