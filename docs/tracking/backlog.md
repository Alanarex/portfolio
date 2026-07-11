# Initial Backlog

## M0 — Repository and governance

### PORT-000 — Audit existing v2 prototype

Status: READY

Acceptance criteria:

- [ ] map current React/TypeScript/Tailwind/Three.js files;
- [ ] classify each material file as retain, adapt, migrate, archive or remove;
- [ ] record useful visual and 3D assets;
- [ ] identify security, licensing and performance concerns;
- [ ] produce migration recommendation.

### PORT-001 — Resolve scaffold decisions

Status: BLOCKED

Acceptance criteria:

- [ ] public rendering confirmed;
- [ ] dashboard frontend confirmed;
- [ ] Three.js runtime confirmed;
- [ ] database selected;
- [ ] package manager selected;
- [ ] PHP test framework selected;
- [ ] initial auth scope selected;
- [ ] hosting target documented.

### PORT-002 — Scaffold Laravel modular foundation

Status: BACKLOG

Depends on: PORT-000, PORT-001.

Acceptance criteria:

- [ ] Laravel 13 application runs locally;
- [ ] modular loader strategy documented and implemented;
- [ ] Docker services run;
- [ ] database and Redis health checks pass;
- [ ] `.env.example` complete;
- [ ] setup documentation validated on a clean checkout.

### PORT-003 — Configure quality and CI

Status: BACKLOG

Acceptance criteria:

- [ ] Pint;
- [ ] Larastan/PHPStan;
- [ ] PHPUnit/Pest;
- [ ] ESLint;
- [ ] Stylelint;
- [ ] frontend build;
- [ ] GitHub Actions pipeline;
- [ ] branch protection requirements documented.

## M1 — CMS and public HTML

### AUTH-001 — Private administrator authentication
### PORT-010 — Portfolio content model
### PORT-011 — Media and CV management
### PORT-012 — Public HTML landing page
### PORT-013 — Project cards and case studies
### PORT-014 — Personal Interests
### PORT-015 — SEO and contact

## M2 — 3D preview

### PORT3D-001 — Temporary low-poly scene integration
### PORT3D-002 — Fullscreen and fallback behavior
### PORT3D-003 — Avatar interaction state machine
### PORT3D-004 — Hotspot navigation and dashboard configuration
### PORT3D-005 — Final Blender asset handoff and performance validation
