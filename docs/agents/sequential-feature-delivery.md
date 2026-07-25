# Issue-by-issue delivery

GitHub is the live issue queue. `docs/tracking/execution-queue.md` records dependency order and exact branches.

For `next issue`:

1. Prefer an open `status:in-progress` PORT issue.
2. Otherwise choose the first open `status:ready` PORT issue whose dependency issue is completed and whose dependency PR is merged into the active integration branch.
3. Read only that issue, run the context and route scripts, and verify repository state.
4. Deliver one issue on its recorded branch with relevant tests, documentation and PR evidence.
5. The coordinator alone publishes and changes GitHub state.
6. Use gated squash/auto-merge only when requested or already authorized and all checks/reviews pass.
7. End the chat after the selected issue's handoff or merge. The next issue starts in a fresh chat with `next issue`.

Do not skip dependencies, implement a second issue, carry old feature context forward, bypass protections or invent PORT IDs. After PORT-011, use the existing release-readiness rule rather than inventing PORT-012.
