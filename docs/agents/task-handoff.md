# Agent Task Handoff Protocol

Each subagent must return:

```markdown
## Scope completed

## Files inspected or changed

## Findings / implementation

## Tests run

## Risks

## Unresolved items

## Recommended next action
```

## Write-agent assignment template

```text
Feature: PORT3D-001
Role: threejs_worker
Worktree/branch: agent/PORT3D-001/threejs
Owned paths:
- Modules/Portfolio/Resources/js/three-room/**
- Modules/Portfolio/Resources/scss/_three-room.scss

Forbidden paths:
- database/migrations/**
- shared design tokens
- unrelated modules

Acceptance criteria:
- ...

Required checks:
- npm run lint
- npm run test
- npm run build
```

## Integration rule

Subagents do not merge their own branches into the integration branch. The orchestrator reviews and integrates them.
