# Agent operating instructions

## Required context

Before starting work, read:

1. `.ai-dev/project.yaml`
2. `.ai-dev/policies.yaml`
3. `docs/product/overview.md`
4. `docs/architecture/overview.md`
5. `docs/progress/current-state.md`
6. The assigned backlog task

## Execution rules

- Work on one bounded task at a time.
- Use an isolated branch or worktree.
- Do not modify unrelated functionality.
- Do not invent product requirements.
- Run `.ai-dev/bin/verify` before completion.
- Update documentation and project state when behaviour changes.
- Open a pull request for completed work.

## Safety boundaries

Never expose secrets, modify production data, bypass failing tests, lower quality thresholds, or deploy to production without explicit approval.
