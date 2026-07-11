# Sequential Feature Delivery

## Purpose

This document defines how Codex selects, delivers, validates, publishes, merges, closes and advances through the ordered PORT issues.

## Controller

- GitHub controller issue: `#17`
- Integration branch: `main`
- One feature issue = one branch = one pull request
- Current sequence: PORT-002 through PORT-011

## Required local tooling

```powershell
winget install --id GitHub.cli
gh auth login
gh auth status
```

Codex must stop before publication if `gh auth status` fails and no equivalent GitHub connector is available.

## Selecting the next feature

1. Read `AGENTS.md`, `PROJECT_STATUS.md`, this document and `docs/tracking/execution-queue.md`.
2. Read controller issue `#17`.
3. Inspect the ordered queue.
4. Select the first open issue whose dependency issue is closed as completed and whose merge is present on `main`.
5. Read the complete issue body, comments, acceptance criteria, branch name, exclusions and merge contract.
6. Do not skip issues or invent a different scope.

Useful commands:

```powershell
git checkout main
git pull --ff-only origin main
gh issue view 17 --repo Alanarex/portfolio
gh issue list --repo Alanarex/portfolio --state open --search 'in:title [PORT-'
```

## Execution-status markers

Issue bodies use one of these markers:

- `READY_AFTER: #N`: executable after issue `#N` is completed and merged.
- `HUMAN_GATE_AFTER: #N`: work may produce a draft PR after `#N`, but merge and continuation require explicit product-owner approval.

A human-gated issue must not be treated as approved merely because CI is green.

## Start protocol

Before writes:

1. verify clean worktree;
2. update `main` with fast-forward only;
3. create the exact branch named in the issue;
4. read relevant ADRs, product docs, module README files and tests;
5. run read-only explorer/architect/QA analysis when the feature is non-trivial;
6. define path ownership before starting write agents.

Do not create a different branch name unless the issue is updated first.

## Implementation protocol

- Use the `feature-delivery` skill.
- Maximum two concurrent write agents.
- Use isolated worktrees for concurrent writers.
- One explicit owner per path.
- Serialize root manifests, routes, migrations, design tokens and frontend entry points.
- Implement only the issue scope.
- Add tests and documentation in the same feature.
- Record material architectural decisions in an ADR.

## Quality protocol

Inspect actual scripts before assuming command names.

At minimum, run all relevant equivalents of:

```powershell
docker compose up -d
docker compose ps
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse
npm run lint
npm run stylelint
npm run test
npm run build
```

Also run when applicable:

- migration rollback;
- Playwright critical flows;
- accessibility checks;
- queue and scheduler checks;
- upload-security tests;
- SEO validation;
- Three.js asset and runtime budgets.

Never weaken a gate to obtain a green result.

## Publication protocol

After implementation and local validation:

```powershell
git status
git diff --check
git add -A
git commit -m '<conventional commit message>'
git push -u origin <issue-branch>
gh pr create --draft --base main --head <issue-branch> --title '<title>' --body 'Closes #<issue-number>'
```

The PR body must include:

- completed acceptance criteria;
- exact commands and results;
- migrations and rollback result;
- screenshots for visual changes;
- security/accessibility/performance evidence where applicable;
- risks and unresolved items.

## Merge protocol

The orchestrator may merge only when all of the following are true:

1. every acceptance criterion is complete or explicitly documented as blocked;
2. all required checks are green;
3. no unresolved QA or review finding remains;
4. the branch is current with `main`;
5. no secrets or unrelated changes are present;
6. the issue merge contract permits automatic merge;
7. any required human gate is explicitly approved.

Preferred command:

```powershell
gh pr ready <pr-number> --repo Alanarex/portfolio
gh pr checks <pr-number> --repo Alanarex/portfolio --watch
gh pr merge <pr-number> --repo Alanarex/portfolio --squash --delete-branch
```

If branch protection or review policy prevents merge, stop and report the exact blocker. Do not bypass protection.

## Closure and continuation

A PR containing `Closes #N` should close the issue when merged. Verify it:

```powershell
gh issue view <issue-number> --repo Alanarex/portfolio --json state,stateReason,url
git checkout main
git pull --ff-only origin main
```

Then:

1. update `PROJECT_STATUS.md` and `docs/tracking/execution-queue.md` in the feature before merge;
2. update controller issue `#17` with the completed PR and checks;
3. remove completed worktrees;
4. select the next executable issue;
5. create a new branch and new focused Codex task/thread for the next issue.

Do not carry implementation context through uncommitted files between features.

## Mandatory stop conditions

Stop, do not merge and do not continue when:

- tests or required checks fail;
- security, privacy or data-loss ambiguity remains;
- a migration is destructive or rollback is not verified;
- production credentials or production deployment are involved;
- the issue scope conflicts with an accepted ADR;
- the next issue is human-gated and approval is missing;
- visual approval is required;
- final Blender assets, licensing or provenance are incomplete;
- `main` changed in a way that invalidates the implementation;
- a feature cannot be completed without inventing product requirements.

## End of initial sequence

PORT-011 is the final issue in this delivery sequence. After it is merged, close controller issue `#17` and create a separate release-planning issue. Do not invent PORT-012 automatically.
