# Portfolio agent contract

## Default command: `next issue`

When the user says `next issue`, own one GitHub issue end to end:

1. Inspect open PORT issues and milestones. Resume an issue labelled `status:in-progress`; otherwise select the first dependency-unblocked issue labelled `status:ready` in `docs/tracking/execution-queue.md` order.
2. Extract its `PORT-NNN` ID, read the issue, then run `bash scripts/context-for-issue.sh PORT-NNN` and read only the listed files.
3. Run `bash scripts/route-for-issue.sh PORT-NNN`. The live issue may raise risk, never silently lower it.
4. Verify the issue dependency is closed and its PR is merged into the active integration branch. If GitHub, the queue and progress files disagree, reconcile them before coding.
5. Use the issue's exact branch. Implement, test, update evidence/status, commit, push and open or update one PR.
6. Stop after that issue's handoff or merge. A new chat and another `next issue` select the next item.

GitHub is the live queue. Issues define scope and acceptance criteria; PRs contain evidence. `docs/tracking/execution-queue.md`, `PROJECT_STATUS.md` and `docs/progress/current-state.md` are compact repository caches and must agree at handoff.

## Progressive context

- Start with this file, the selected issue and the output of `scripts/context-for-issue.sh`.
- Inspect nearby code, module READMEs and tests with targeted search.
- Read matching light and dark references for UI routes/states; do not scan all screenshots.
- Do not preload the full roadmap, all ADRs, all content files or completed issue bodies.
- Use Git history and diffs for recent work.

## Portfolio boundaries

- Active integration branch: `release/v3.0.0/main` unless GitHub identifies a newer repository-approved release baseline.
- Laravel 13 is the backend and HTTP boundary. Modules own business rules, persistence, policies, routes and tests.
- The public Lovable-derived React/TanStack application lives under `resources/js/portfolio/`; preserve its interaction and visual intent while replacing fixtures through typed Laravel readers.
- Vue 3/Inertia owns the private single-administrator CMS.
- French is the default public locale; public features support English.
- Light and dark themes are separately designed. Reuse the portrait and theme-specific logos in `docs/assets/brand/`.
- Essential public content must remain available without JavaScript or WebGL. Three.js stays optional, isolated, lazy-loaded and accessible.
- Never publish placeholder identity data, unverified claims, private repositories, secrets or sensitive contact values.
- New feed endpoints use cursor pagination and never load the whole feed.
- Production deployment and production data changes require explicit approval.

Detailed product, architecture, design, content, testing and deployment documents remain authoritative and are loaded through `.agents/context-map.tsv` only when relevant.

## Execution and routing

- One issue, one chat, one branch and one PR.
- Single-agent execution is the default. The primary coordinator alone edits shared state, commits, pushes, opens PRs and changes GitHub metadata.
- `.agents/model-routing.tsv` defines the conservative route per PORT issue.
- `direct`: the coordinator implements and validates.
- `delegate-terra`: at most one bounded, low-risk implementation task may use `portfolio_terra_worker`; the coordinator inspects and validates the result.
- `review-high`: the coordinator implements and tests, then uses `portfolio_risk_reviewer` once for read-only review.
- Never delegate migrations, authentication/authorization, privacy, uploads, external side effects, shared contracts or unclear acceptance criteria to a writing subagent.
- Never run two writing agents concurrently or let a subagent change GitHub state.

## Completion

- Preserve unrelated user changes and use the issue's exact branch/base.
- Run targeted checks, then `.ai-dev/bin/verify`; add migration rollback, browser/accessibility, security, visual or 3D checks when applicable.
- Do not weaken tests or quality thresholds.
- Update `PROJECT_STATUS.md`, `docs/progress/current-state.md` and the queue only when their facts change.
- Use Conventional Commits. PRs must include `Closes #N`, exact validation evidence, risks and relevant screenshots.
- Follow the repository's gated squash/auto-merge policy. Never bypass branch protection, reviews or failing/pending checks.
- Run `bash scripts/validate-codex-workflow.sh` whenever routing, context, agent or queue configuration changes.

Stop rather than inventing a product decision, personal fact, credential, external authorization or missing dependency.
