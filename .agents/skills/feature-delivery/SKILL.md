---
name: feature-delivery
description: Deliver the next executable PORT feature from GitHub issue through implementation, quality gates, draft PR, controlled merge, issue closure and queue advancement. Use for non-trivial tracked work; do not use for tiny isolated edits.
---

1. Read root and nested `AGENTS.md` files.
2. Read `PROJECT_STATUS.md`, `docs/agents/sequential-feature-delivery.md`, `docs/tracking/execution-queue.md` and controller issue `#17`.
3. Select only the first executable issue in the ordered queue. Verify its dependency issue is completed and merged into `main`.
4. Read the complete feature issue, comments, branch name, acceptance criteria, exclusions, suggested agents and merge contract.
5. Verify `main` is current with `git pull --ff-only origin main`, the worktree is clean and GitHub CLI/connector access is functional.
6. Create the exact issue branch. Do not reuse an old feature branch or change the issue scope silently.
7. Read relevant ADRs, product documents, module READMEs and existing tests.
8. Use explorer, architect and QA subagents for non-trivial analysis. Wait for their handoffs before writes.
9. Define path ownership. Use separate worktrees for independent writers, maximum two concurrent write agents and no overlapping paths.
10. Implement the smallest coherent solution satisfying every acceptance criterion. Add tests, migrations, documentation and ADRs where required.
11. Run targeted checks first, then every relevant repository quality gate. Never weaken checks to obtain a pass.
12. Update `PROJECT_STATUS.md` and `docs/tracking/execution-queue.md` in the feature branch.
13. Commit with a conventional message, push the exact feature branch and open a draft PR containing `Closes #<issue-number>` plus commands, results, risks and evidence.
14. Resolve CI/review findings and ensure the branch is current with `main`.
15. Merge only when the issue merge contract permits it, all checks are green, no QA findings remain, no secrets/unrelated changes exist and all required human gates are explicitly approved.
16. Prefer squash merge and branch deletion. Never bypass branch protection.
17. Verify the issue closed after merge, update controller issue `#17`, clean completed worktrees and pull updated `main`.
18. Start a new focused task/thread for the next executable issue. Stop at human gates or any mandatory stop condition.
19. After PORT-011, stop and create release planning. Do not invent PORT-012 automatically.
