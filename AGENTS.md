# Agent operating instructions

## V3 mission

Build the Laravel 13 modular-monolith implementation of the social portfolio described in
`docs/product/v3-social-portfolio-spec.md`. The Lovable application is a UI/UX prototype,
not the production architecture. Preserve its interaction and visual intent while moving
data, authentication, integrations, caching, queues, and administration into Laravel.

## Required context

Before starting work, read:

1. `.ai-dev/project.yaml`
2. `.ai-dev/policies.yaml`
3. `docs/product/overview.md`
4. `docs/architecture/overview.md`
5. `docs/progress/current-state.md`
6. The assigned backlog task
7. `docs/design/lovable-reference.md` when changing public UI
8. `docs/readiness/v3-development-handoff.md`
9. `docs/design/screenshots/README.md` and both referenced theme captures before changing UI
10. `docs/design/lovable-code-integration.md` before changing the React/Laravel frontend boundary

## Execution rules

- Work on one bounded task at a time.
- Use an isolated branch or worktree.
- Do not modify unrelated functionality.
- Do not invent product requirements.
- French is the default language; every public feature must support English.
- Light and dark themes are separate designed themes, not automatic color inversion.
- Use the supplied portrait and logos in `docs/assets/brand/`; never generate replacements.
- Do not copy placeholder identity data from the Lovable prototype (for example “Alex Karim”).
- Treat `resources/js/portfolio/data/portfolio.ts` as temporary visual fixtures; never persist or publish its unverified claims.
- Preserve Laravel as the application backend. Port Lovable behavior inside `resources/js/portfolio/` and replace fixtures through typed Laravel APIs incrementally.
- Treat screenshots as visual references, not acceptance criteria without an associated route/state entry.
- For UI work, compare the matching light and dark full-page references for every affected
  audience mode before implementation and again before completion. If a state is documented as
  unavailable, do not infer its appearance; record the gap and obtain a verified capture first.
- UI pull requests must name the screenshot routes/states used and include comparison evidence for both themes.
- New feed endpoints must use cursor pagination and must never load the complete feed.
- Public profile values must come from persistence/configuration, never component constants.
- Run `.ai-dev/bin/verify` before completion.
- Update documentation and project state when behaviour changes.
- Open a pull request for completed work.

## Safety boundaries

Never expose secrets, modify production data, bypass failing tests, lower quality thresholds, or deploy to production without explicit approval.
