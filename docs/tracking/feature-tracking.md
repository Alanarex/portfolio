# Feature Tracking

## Hierarchy

```text
Project
└── Milestone
    └── Epic
        └── Feature
            ├── Technical Tasks
            ├── Acceptance Criteria
            ├── Tests
            ├── Pull Request
            ├── Deployment
            └── Release
```

## Statuses

- BACKLOG
- READY
- IN_PROGRESS
- BLOCKED
- IN_REVIEW
- TESTING
- READY_FOR_RELEASE
- DEPLOYED
- DONE

## Priorities

- P0 Critical
- P1 High
- P2 Medium
- P3 Low

## Prefixes

- AUTH
- PORT
- PORT3D
- DASH
- DEMO
- DEPLOY
- LIC
- TASK
- GITHUB
- GITLAB
- OBS

## Feature template

```markdown
# PORT-001 — Feature title

## Status
BACKLOG

## Priority
P2

## Context

## Objective

## Scope

## Exclusions

## Acceptance Criteria
- [ ] Criterion 1

## Technical Tasks
- [ ] Task 1

## Tests
- [ ] Unit
- [ ] Integration
- [ ] Feature
- [ ] Browser/accessibility where applicable

## Dependencies

## Related Issue / PR

## Deployment

## Handoff / Notes
```

## Durable tracking rule

- GitHub Issue is the active work item;
- feature document provides stable detailed specification when needed;
- Pull Request contains implementation evidence;
- `PROJECT_STATUS.md` summarizes current phase;
- chat is not the durable status store.
