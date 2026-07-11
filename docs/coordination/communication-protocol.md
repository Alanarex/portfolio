# Communication Protocol

## Durable source of truth

Use, in priority order:

1. accepted ADRs for architecture decisions;
2. GitHub Issues for scoped work and acceptance criteria;
3. Pull Requests for implementation evidence and review;
4. `PROJECT_STATUS.md` for current phase and blockers;
5. module README files for local contracts;
6. chat only for discussion before durable recording.

## Rule for every member

No material decision remains only in ChatGPT, Codex, Blender notes or a private message. The owner records it in the relevant issue, ADR, PR or handoff document.

## Status vocabulary

- `PROPOSED`
- `READY`
- `IN_PROGRESS`
- `BLOCKED`
- `IN_REVIEW`
- `TESTING`
- `READY_FOR_RELEASE`
- `DEPLOYED`
- `DONE`

## Change notification

A member must explicitly report:

- changed files or assets;
- changed contracts or node names;
- migrations or environment changes;
- tests performed;
- performance impact;
- unresolved risks;
- next required owner.

## Conflict rule

When documents conflict:

1. accepted ADR wins for architecture;
2. latest approved issue acceptance criteria wins for feature scope;
3. repository implementation and tests describe current behavior;
4. the orchestrator resolves remaining ambiguity and records the decision.
