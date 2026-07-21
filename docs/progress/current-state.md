# Current state

- Laravel 13 remains the backend and HTTP entry point.
- Lovable's React 19/TanStack frontend is integrated through Vite in PR #23.
- Public routes, light/dark themes, French/English preferences and four audience modes compile successfully.
- CI verification: PHPUnit, Pint, ESLint, Stylelint and production Vite build pass.
- Docker Compose and PHPStan/Larastan are not yet configured; their invalid baseline CI steps were removed.
- Next executable feature after PR #23 merges: issue #10 (PORT-005 profile and settings CMS).
