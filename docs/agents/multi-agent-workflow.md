# Selective agent routing

The coordinator works alone by default and owns all edits, integration, validation, commits, pushes, PRs and GitHub state.

Only one supporting agent is allowed per issue:

- `delegate-terra`: one bounded, low-risk implementation task with explicit paths and acceptance criteria;
- `review-high`: one read-only risk review after implementation and tests.

Do not delegate migrations, authentication, authorization, privacy, uploads, external side effects, shared contracts or ambiguous scope to a writing agent. Supporting agents never commit, push, open or merge PRs, alter issues, spawn agents or broaden scope.

The checked-in route is a minimum safety level. Live issue risk may raise it; it may not be lowered silently. Validate the coordinator's final diff regardless of delegation.
