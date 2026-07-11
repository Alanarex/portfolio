# Sequential Feature Delivery

## Purpose

Define how Codex continuously selects, delivers, validates, publishes, merges, closes and advances through the ordered PORT issues.

## Controller

- GitHub controller issue: `#17`
- Integration branch: `main`
- One issue = one branch = one pull request
- Autonomous sequence: PORT-003 through PORT-011
- Governing decision: `docs/architecture/decisions/ADR-007-autonomous-continuous-delivery.md`

## Required local tooling

```powershell
winget install --id GitHub.cli
gh auth login
gh auth status
```

Codex may use an equivalent authenticated GitHub connector. Publication and merge access must work before the chain starts.

## Continuous selection

1. Read `AGENTS.md`, `PROJECT_STATUS.md`, ADR-007, this document and `docs/tracking/execution-queue.md`.
2. Read controller issue `#17`.
3. Select the first open issue whose dependency is completed and whose merge exists on `main`.
4. Read the complete issue, comments, exact branch, exclusions, acceptance criteria, agents and merge contract.
5. Deliver only that issue.
6. After successful merge and closure, immediately repeat from step 1 for the next issue.
7. Do not wait for another prompt or a new task/thread between issues.
8. Do not skip issues or invent a different scope.

## Start protocol

Before writes:

1. verify the main worktree is clean;
2. `git checkout main`;
3. `git pull --ff-only origin main`;
4. create the exact issue branch;
5. read relevant ADRs, product docs, module README files and tests;
6. run read-only explorer/architect/QA analysis for non-trivial work;
7. define path ownership before write agents start.

## Implementation protocol

- Use the `feature-delivery` skill.
- Maximum two concurrent write agents.
- Use isolated worktrees for concurrent writers.
- One explicit owner per path.
- Serialize root manifests, routes, migrations, design tokens and frontend entry points.
- Implement only the current issue scope.
- Add tests and documentation in the same feature.
- Record material architectural decisions in ADRs.
- Use ADR-007 conservative defaults for ordinary visual, privacy and v1 3D decisions.

## Quality protocol

Inspect actual scripts before assuming command names.

Run every relevant equivalent of:

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
- Three.js asset/runtime budgets;
- secret and private-data scans.

Never weaken a gate to obtain a green result.

## Publication protocol

After implementation and local validation:

```powershell
git status
git diff --check
git add -A
git commit -m '<conventional commit>'
git push -u origin <issue-branch>
gh pr create --draft --base main --head <issue-branch> --title '<title>' --body 'Closes #<issue-number>'
```

The PR must include:

- acceptance criteria status;
- exact commands/results;
- migrations and rollback evidence;
- screenshots for visual changes;
- security/accessibility/performance evidence;
- risks and unresolved items.

## Merge protocol

The orchestrator may mark ready and squash-merge only when:

1. every acceptance criterion is satisfied;
2. all required checks are green;
3. no unresolved QA/review finding remains;
4. the branch is current with `main`;
5. no secrets, private source code or unrelated changes exist;
6. rollback is verified where applicable;
7. ADR-007 permits any selected fallback;
8. branch protection and required reviews permit merge.

```powershell
gh pr ready <pr-number> --repo Alanarex/portfolio
gh pr checks <pr-number> --repo Alanarex/portfolio --watch
gh pr merge <pr-number> --repo Alanarex/portfolio --squash --delete-branch
```

Never bypass branch protection.

## Closure and immediate continuation

After merge:

```powershell
gh issue view <issue-number> --repo Alanarex/portfolio --json state,stateReason,url
git checkout main
git pull --ff-only origin main
```

Then:

1. verify issue closure;
2. update controller issue `#17` with PR and checks;
3. remove completed worktrees/branches;
4. read the updated queue;
5. create the next issue branch;
6. immediately continue delivery in the same orchestration.

Do not carry uncommitted files between features.

## Autonomous decision defaults

Follow ADR-007:

- choose an accessible dark-first professional visual direction;
- use conservative privacy defaults;
- use original procedural or clearly licensed v1 3D assets;
- use a generic stylized developer avatar when personal likeness references are absent;
- do not pause for ordinary aesthetic preference selection.

## Hard stop conditions

Stop only when a blocker cannot be safely resolved after reasonable repair attempts:

- tests/CI remain failing;
- security, privacy, licensing or data-loss risk remains unresolved;
- migration rollback cannot be verified;
- production credentials or production deployment approval are required;
- GitHub permission or branch protection blocks publication/merge;
- an accepted ADR conflict requires a product-level reversal;
- required external tooling/assets are unavailable and no allowed procedural/licensed fallback exists;
- `main` changes invalidate the implementation.

Record the exact blocker in the issue and controller before stopping.

## End of sequence

PORT-011 ends the initial delivery chain. After it merges:

1. close controller issue `#17`;
2. create a release-readiness issue for full regression, production configuration, deployment, backups, monitoring and rollback;
3. do not invent PORT-012 automatically.
