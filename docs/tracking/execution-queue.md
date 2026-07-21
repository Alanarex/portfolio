# PORT Execution Queue

## Controller

GitHub issue: `#17`

Mode: **autonomous continuous delivery** under ADR-007.

## Selection rule

Codex selects the first issue in this table that:

1. remains open;
2. has its dependency issue closed as completed;
3. has the dependency PR merged into `main`.

After each successful merge, Codex immediately starts the next executable issue without waiting for another prompt.

Do not change order without updating this file, issue `#17` and affected issue dependencies.

## Queue

| Order | Feature | Issue | Branch | Dependency | Gate | State |
|---:|---|---:|---|---|---|---|
| 1 | PORT-002 — Laravel modular foundation | #6 | `feature/PORT-002-laravel-foundation` | PORT-001 | Automatic if green | Done |
| 2 | PORT-003 — Secure admin authentication | #8 | `feature/PORT-003-admin-auth` | #6 | Automatic if green | Done |
| 3 | PORT-004 — Design system and layouts | #9 | `feature/PORT-004-design-system` | #8 | ADR-007 visual defaults | Done |
| 4 | PORT-005 — Profile and settings CMS | #10 | `feature/PORT-005-profile-settings` | #9 | Automatic if green | Ready |
| 5 | PORT-006 — Career, education and skills CMS | #11 | `feature/PORT-006-career-skills` | #10 | Automatic if green | Planned |
| 6 | PORT-007 — Projects, case studies and media | #12 | `feature/PORT-007-projects-media` | #11 | Automatic if green | Planned |
| 7 | PORT-008 — Public portfolio and case studies | #13 | `feature/PORT-008-public-portfolio` | #12 | Screenshots + QA, no manual gate | Planned |
| 8 | PORT-009 — Contact, SEO, CV and privacy | #14 | `feature/PORT-009-contact-seo` | #13 | ADR-007 privacy defaults | Planned |
| 9 | PORT-010 — Three.js configuration and placeholder | #15 | `feature/PORT-010-threejs-config` | #14 | Automatic if green | Planned |
| 10 | PORT-011 — Final v1 3D integration | #16 | `feature/PORT-011-blender-integration` | #15 | ADR-007 procedural/licensed fallback | Planned |

## State values

- `Planned`: dependency incomplete.
- `Ready`: dependency complete.
- `In progress`: branch/worktree exists and implementation started.
- `Review`: PR exists.
- `Blocked`: ADR-007 hard blocker recorded.
- `Done`: PR merged, issue closed and `main` updated.

## Update responsibility

The orchestrator updates this table inside each feature PR when a feature moves to `Review` or `Done`. After merge it immediately advances the next issue to `Ready` and starts it.

## Autonomous defaults

### PORT-004 visual direction

- dark-first professional interface;
- neutral navy/slate surfaces;
- one restrained warm accent;
- accessible system-first typography or an openly licensed font;
- responsive layouts, strong focus states and reduced motion;
- screenshots reviewed by QA for accessibility and consistency.

### PORT-009 privacy

- exact address and phone hidden;
- contact form public;
- email public only when explicitly configured;
- no analytics vendor by default;
- no private GitHub/GitLab activity;
- CV download only when a verified published file exists;
- minimal/no contact-message persistence.

### PORT-011 assets

- original procedural low-poly assets or clearly licensed assets only;
- provenance recorded;
- generic stylized developer avatar when personal references are absent;
- required semantic nodes and animation contracts preserved;
- personalized likeness deferred to an optional later asset revision;
- no audio, physics, heavy post-processing, dynamic hair or cloth.

## Hard blocker rule

Codex stops only for an ADR-007 hard blocker that cannot be resolved safely after reasonable attempts. It records the blocker in the current issue and issue `#17`.

## After PORT-011

Create a release-readiness issue covering regression validation, production configuration, deployment, backups, monitoring and rollback. Do not invent PORT-012 automatically.
