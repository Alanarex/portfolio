# Context Strategy

## Permanent root context

- `README.md`
- `AGENTS.md`
- relevant nested `AGENTS.md`
- current feature file
- relevant module README

## Task-specific context

### Public portfolio

- `docs/product/public-portfolio.md`
- `docs/ui/design-system.md`
- `docs/ui/screen-map.md`

### 3D room

- `docs/product/three-d-preview.md`
- `docs/ui/interactions.md`
- asset manifest and node naming contract

### Dashboard

- `docs/product/dashboard.md`
- relevant mockups;
- relevant module README.

### Infrastructure

- `docs/operations/environments.md`
- `docs/operations/ci-cd.md`
- `docs/operations/observability.md`.

## Rules

- do not preload all documentation into every task;
- use skills for repeatable workflows;
- use nested `AGENTS.md` files for local module rules;
- summarize exploration before implementation;
- store decisions in ADRs;
- store task state in feature files, not chat history.
