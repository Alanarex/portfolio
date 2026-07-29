# Current state

- Laravel 13 remains the backend and HTTP entry point.
- Lovable's React 19/TanStack source remains preserved as the visual and interaction reference.
- PORT-008 replaces the hardcoded public mount with localized, HTML-first Blade composition while
  keeping Vue/Inertia isolated to the private dashboard.
- PORT-005 / issue #10 is merged through PR #24.
- PORT-006 / issue #11 is merged through PR #27 with separate Career
  and Skills module boundaries, authenticated Vue/Inertia aggregate CRUD and keyboard ordering,
  localized publication controls, verified achievement filtering, allowlisted public readers,
  deterministic cache invalidation and redacted audit events.
- Initial content imports exactly the verified career, education, TOEIC, spoken-language and
  59-skill taxonomy facts from `docs/content/`. Records remain drafts because verified English
  translations are unavailable; no prototype claims or Handicapacité project metrics are seeded.
- PORT-007 / issue #12 is squash-merged through PR #28 into `release/v3.0.0/main`; issue #12 is
  closed after green PostgreSQL migration rollback, 28 tests / 360 assertions, Pint, Larastan,
  frontend gates and focused security review.
- PORT-008 / issue #13 is implemented on `feature/PORT-008-public-portfolio` with localized
  HTML-first landing, project index, stable-slug case studies, responsive navigation, semantic
  structure, dark/light themes, responsive brand media and publication-checked image delivery.
- New PHPUnit coverage checks SSR output, locale and publication gates, incomplete translations,
  stable slugs, 404 behavior and private media authorization.
- New Playwright coverage checks no-JavaScript content, keyboard flow, axe WCAG A/AA, responsive
  mobile layouts and light/dark implemented captures.
- Local verification: workflow validation, all changed PHP files parse, ESLint passes, Stylelint
  passes, the production Vite build passes, Playwright discovers all browser flows and
  `git diff --check` passes.
- Remaining blocker: PHP, Composer and Docker are unavailable in Work Mode; PHPUnit, Pint,
  Larastan, Composer audit, PostgreSQL checks and browser rendering require GitHub CI.
- Essential public content is rendered before JavaScript. Git activity, CV delivery, final contact
  behavior and final WebGL remain honest placeholders for PORT-009 through PORT-011.
- Docker Compose remains intentionally absent from the active V3 baseline; PostgreSQL behavior
  is verified by CI.
- PR #27 merged PORT-006 into `release/v3.0.0/main`; issue #11 is closed as completed.
- Next gate: publish the PORT-008 draft PR, verify all CI/accessibility gates and commit the
  generated implementation screenshots.
- After PORT-008 merge, issue #14 / PORT-009 is the next queue item for a fresh `next issue` chat.
