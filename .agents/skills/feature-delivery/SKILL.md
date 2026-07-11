---
name: feature-delivery
description: Deliver the next executable PORT feature from GitHub issue through implementation, quality gates, PR, controlled merge, issue closure and immediate queue advancement. Use for all non-trivial tracked PORT work.
---

1. Read root and nested `AGENTS.md` files.
2. Read `PROJECT_STATUS.md`, ADR-007, `docs/agents/sequential-feature-delivery.md`, `docs/tracking/execution-queue.md` and controller issue `#17`.
3. Select only the first executable issue in the ordered queue. Verify its dependency is completed and merged into `main`.
4. Read the complete feature issue, comments, exact branch, acceptance criteria, exclusions, suggested agents and merge contract.
5. Verify `main` is current, the worktree is clean and GitHub publication/merge access works.
6. Create the exact issue branch. One issue equals one branch and one pull request.
7. Read relevant ADRs, product docs, module READMEs and tests.
8. Use explorer, architect and QA subagents for non-trivial analysis; wait for their handoffs before writes.
9. Define path ownership. Use separate worktrees for independent writers, maximum two concurrent write agents and no overlapping paths.
10. Implement the smallest coherent solution satisfying every acceptance criterion. Add tests, migrations, documentation and ADRs where required.
11. Use ADR-007 defaults for ordinary visual, privacy and v1 3D decisions instead of waiting for user input.
12. Run targeted checks first, then every relevant local and CI quality gate. Never weaken checks to obtain a pass.
13. Update `PROJECT_STATUS.md` and `docs/tracking/execution-queue.md` in the feature branch.
14. Commit conventionally, push the exact branch and open a draft PR containing `Closes #<issue-number>` plus commands, results, risks and evidence.
15. Resolve CI/review findings and update the branch from `main`.
16. Squash-merge and delete the branch when all criteria/checks pass, no QA findings remain, no secrets/unrelated changes exist and branch protection permits it.
17. Verify issue closure, update controller issue `#17`, clean worktrees and pull updated `main`.
18. Immediately select and start the next executable PORT issue in the same orchestration. Do not wait for a new prompt or create a new chat.
19. Stop only for an ADR-007 hard blocker that cannot be resolved safely after reasonable repair attempts.
20. After PORT-011, close the controller and create a release-readiness issue. Do not invent PORT-012 automatically.
