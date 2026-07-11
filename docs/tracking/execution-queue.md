# PORT Execution Queue

## Controller

GitHub issue: `#17`

## Selection rule

Codex selects the first issue in this table that:

1. remains open;
2. has its dependency issue closed as completed;
3. has the dependency PR merged into `main`;
4. is not waiting for an unsatisfied human gate.

Do not change order without updating this file, issue `#17` and the affected issue dependencies.

## Queue

| Order | Feature | GitHub issue | Branch | Dependency | Gate | State |
|---:|---|---:|---|---|---|---|
| 1 | PORT-002 — Laravel modular foundation | #6 | `feature/PORT-002-laravel-foundation` | PORT-001 complete | Automatic if green | Done |
| 2 | PORT-003 — Secure admin authentication | #8 | `feature/PORT-003-admin-auth` | #6 | Automatic if green | Ready |
| 3 | PORT-004 — Design system and layouts | #9 | `feature/PORT-004-design-system` | #8 | Human visual approval | Planned |
| 4 | PORT-005 — Profile and settings CMS | #10 | `feature/PORT-005-profile-settings` | #9 | Automatic if green | Planned |
| 5 | PORT-006 — Career, education and skills CMS | #11 | `feature/PORT-006-career-skills` | #10 | Automatic if green | Planned |
| 6 | PORT-007 — Projects, case studies and media | #12 | `feature/PORT-007-projects-media` | #11 | Automatic if green | Planned |
| 7 | PORT-008 — Public portfolio and case studies | #13 | `feature/PORT-008-public-portfolio` | #12 | Visual evidence required | Planned |
| 8 | PORT-009 — Contact, SEO, CV and privacy | #14 | `feature/PORT-009-contact-seo` | #13 | Privacy choices must be resolved | Planned |
| 9 | PORT-010 — Three.js configuration and placeholder | #15 | `feature/PORT-010-threejs-config` | #14 | Automatic if green | Planned |
| 10 | PORT-011 — Final Blender integration | #16 | `feature/PORT-011-blender-integration` | #15 | Human asset and visual approval | Planned |

## State values

- `Planned`: issue exists but its dependency is not complete.
- `Ready`: dependency is complete and all required human inputs exist.
- `In progress`: branch/worktree exists and implementation has started.
- `Review`: draft or ready PR exists.
- `Blocked`: a mandatory stop condition is active.
- `Done`: PR merged, issue closed and `main` updated.

## Update responsibility

The orchestrator updates this table in the same feature PR whenever a feature moves to `Review` or `Done`. After merge, the next issue may be moved from `Planned` to `Ready` only when its dependency and gates are satisfied.

## Human-gated inputs

### PORT-004

- approved palette;
- typography direction;
- initial public/dashboard layout direction;
- approval of desktop and mobile screenshots before merge.

### PORT-009

- public email/phone/location visibility;
- downloadable CV choice;
- GitHub/GitLab activity visibility;
- analytics/privacy decision.

### PORT-011

- avatar reference photos;
- hairstyle, facial hair, glasses and outfit;
- approved low-poly style;
- room blockout;
- Blender asset package and provenance;
- approval of final preview before merge.

## After PORT-011

Create a release-planning issue covering release candidate validation, production readiness, deployment, backups, monitoring and rollback. Do not automatically invent the next numbered PORT feature.
