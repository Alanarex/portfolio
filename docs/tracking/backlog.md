# Portfolio Delivery Backlog

## Controller

- Sequential controller issue: `#17`
- Detailed queue: `docs/tracking/execution-queue.md`
- Delivery protocol: `docs/agents/sequential-feature-delivery.md`

## Cancelled

### PORT-000 — Audit existing prototype

Cancelled by product decision. Historical implementations remain archived and are not reused.

## Completed decisions

### PORT-001 — Resolve scaffold decisions

Status: Done.

Approved baseline: Laravel 13, PostgreSQL, Redis, Blade SSR, Vue 3 + TypeScript + Inertia, vanilla Three.js + TypeScript, npm, PHPUnit, Docker Compose and OVH VPS target.

## Phase 0 — Foundation

### PORT-002 — Scaffold Laravel 13 modular foundation

GitHub issue: `#6`  
Branch: `feature/PORT-002-laravel-foundation`  
Status: In progress.

Scope:

- clean Laravel 13 scaffold;
- preserve documentation and agent configuration;
- module bootstrap;
- Docker, PostgreSQL, Redis, Nginx, worker, scheduler and Mailpit;
- Blade public shell;
- Vue 3 + TypeScript + Inertia dashboard shell;
- isolated Three.js placeholder and fallback;
- PHPUnit, Pint, Larastan/PHPStan, ESLint, Stylelint and build scripts;
- GitHub Actions CI;
- health checks and request-ID logging.

## Phase 1 — Secure administration and design foundation

### PORT-003 — Secure single-admin authentication

GitHub issue: `#8`  
Branch: `feature/PORT-003-admin-auth`  
Dependency: `#6`.

### PORT-004 — Establish design system and application layouts

GitHub issue: `#9`  
Branch: `feature/PORT-004-design-system`  
Dependency: `#8`  
Gate: product-owner visual approval.

## Phase 2 — Portfolio content management

### PORT-005 — Profile, settings and public identity CMS

GitHub issue: `#10`  
Branch: `feature/PORT-005-profile-settings`  
Dependency: `#9`.

### PORT-006 — Career, education and skills CMS

GitHub issue: `#11`  
Branch: `feature/PORT-006-career-skills`  
Dependency: `#10`.

### PORT-007 — Projects, case studies and media management

GitHub issue: `#12`  
Branch: `feature/PORT-007-projects-media`  
Dependency: `#11`.

## Phase 3 — Public portfolio delivery

### PORT-008 — Public landing page and case-study pages

GitHub issue: `#13`  
Branch: `feature/PORT-008-public-portfolio`  
Dependency: `#12`.

### PORT-009 — Contact, SEO, CV delivery and privacy controls

GitHub issue: `#14`  
Branch: `feature/PORT-009-contact-seo`  
Dependency: `#13`  
Gate: public-contact, CV and privacy choices resolved.

## Phase 4 — Interactive 3D experience

### PORT-010 — 3D scene configuration and interactive placeholder

GitHub issue: `#15`  
Branch: `feature/PORT-010-threejs-config`  
Dependency: `#14`.

### PORT-011 — Final Blender room and personalized avatar integration

GitHub issue: `#16`  
Branch: `feature/PORT-011-blender-integration`  
Dependency: `#15`  
Gate: approved avatar references, blockout, asset package, provenance and final preview.

## After PORT-011

Create a release-planning issue for release-candidate testing, production readiness, deployment, backups, monitoring and rollback. Do not automatically create PORT-012.

## Future platform expansion

These capabilities remain outside the initial PORT-002 → PORT-011 sequence and require separate approved issues:

- GitHub/GitLab activity synchronization;
- tasks, Kanban and calendar;
- statistics and monitoring;
- demos and deployment center;
- reusable module registry;
- licensing;
- mobile API.
