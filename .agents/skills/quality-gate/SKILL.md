---
name: quality-gate
description: Run the portfolio-platform completion gate after implementation: formatting, static analysis, tests, frontend lint/build, accessibility, performance, migration and documentation checks.
---

1. Inspect actual scripts in `composer.json` and `package.json`.
2. Run targeted tests first.
3. Run Pint in check mode.
4. Run Larastan/PHPStan.
5. Run the PHP test suite and coverage requirements.
6. Run ESLint, Stylelint and frontend tests.
7. Build production assets.
8. Run Playwright flows when UI changed.
9. Run accessibility and performance checks when public UI or 3D changed.
10. Verify migration rollback when schema changed.
11. Report exact commands, pass/fail state and blockers. Never hide failures.
