---
name: feature-delivery
description: Start or resume one Portfolio PORT issue safely from the two-word next issue command.
---

1. For `next issue`, query GitHub open PORT issues. Resume `status:in-progress`; otherwise select the first `status:ready`, dependency-unblocked issue in queue order.
2. Read root `AGENTS.md` and the selected issue. Extract `PORT-NNN`; do not scan unrelated issue bodies.
3. Run `bash scripts/context-for-issue.sh PORT-NNN` and read only its output.
4. Run `bash scripts/route-for-issue.sh PORT-NNN`; raise risk for live security, privacy, migration, concurrency, data-integrity, upload, external-side-effect or unclear-scope concerns.
5. Verify dependency merge, active base, exact branch and clean worktree. Reconcile GitHub/cache disagreement before coding.
6. State outcome, exclusions, route and checks. Implement only this issue.
7. The primary coordinator alone changes files shared across the task, commits, pushes, opens/updates PRs and changes GitHub state.
8. Run targeted checks and `.ai-dev/bin/verify`, plus routed risk checks. Update evidence and compact progress caches when facts change.
9. Follow gated merge policy and end after this issue's handoff or merge. Do not start a second issue in the same chat.

Stop rather than inventing a product decision, personal fact, credential, external authorization or dependency state.
