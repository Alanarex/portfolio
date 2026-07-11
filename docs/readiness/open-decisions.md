# Open Decisions

## Scaffold decisions — resolved

The following baseline is accepted for portfolio v2:

1. Public rendering: Laravel Blade/server-rendered pages.
2. Dashboard frontend: Vue 3 + TypeScript with Inertia.
3. Three.js runtime: vanilla Three.js + TypeScript.
4. Database: PostgreSQL.
5. JavaScript package manager: npm.
6. PHP test framework: PHPUnit.
7. Initial authentication: one private administrator, with boundaries prepared for later RBAC.
8. Hosting: OVH VPS with Docker, Nginx, PostgreSQL, Redis, workers, scheduler, monitoring and backups.
9. Existing React prototype: audit first under PORT-000, migrate useful elements, then archive/remove only with documented evidence.

See `docs/architecture/decisions/ADR-005-scaffold-baseline.md`.

## Still blocking final visual implementation

- final color palette;
- typography;
- public portfolio language strategy: French, English or bilingual;
- final featured-project order;
- downloadable CV choice;
- public/private GitHub and GitLab activity policy;
- approved landing-page and dashboard mockups.

## Still blocking final 3D asset production

- current photo references for avatar likeness;
- avatar hairstyle, facial hair, glasses and outfit;
- preferred low-poly style;
- room layout reference;
- asset source strategy: self-created, commissioned or licensed assets;
- confirmation that the first release uses text-only greeting;
- final asset budgets after temporary-scene profiling.

## Not blocking Phase 0 scaffolding

The visual and final Blender decisions above do not block PORT-000 or PORT-002. Temporary assets and placeholders must be used until the final handoff is approved.
