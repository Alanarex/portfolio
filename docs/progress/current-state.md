# Current state

- Laravel 13 remains the backend and HTTP entry point.
- Lovable's React 19/TanStack frontend is integrated through Vite after PR #23.
- Public routes, light/dark themes, French/English preferences and four audience modes compile successfully.
- PORT-005 / issue #10 is merged through PR #24.
- PORT-006 / issue #11 is merged through PR #27 with separate Career
  and Skills module boundaries, authenticated Vue/Inertia aggregate CRUD and keyboard ordering,
  localized publication controls, verified achievement filtering, allowlisted public readers,
  deterministic cache invalidation and redacted audit events.
- Initial content imports exactly the verified career, education, TOEIC, spoken-language and
  59-skill taxonomy facts from `docs/content/`. Records remain drafts because verified English
  translations are unavailable; no prototype claims or Handicapacité project metrics are seeded.
- PORT-007 / issue #12 is implemented on `feature/PORT-007-projects-media` with a module-owned
  reversible schema, administrator project/case-study/media CRUD, filters, ordering, preview,
  encrypted private repository and storage metadata, secure private uploads, draft verified seeds,
  cache invalidation, redacted audits and allowlisted public readers.
- Targeted tests cover authorization, slug stability, publication/verification/locale gates,
  private repository leakage, upload MIME/extension/size rules, generated paths, file cleanup,
  cache invalidation, audit redaction and seed idempotency.
- Local verification: all PHP files parse, ESLint passes, Stylelint passes, the production Vite
  build passes and `git diff --check` passes.
- Remaining blocker: PHP, Composer and Docker are unavailable in Work Mode; PHPUnit, Pint,
  Larastan, Composer audit and PostgreSQL migrate/rollback/re-migrate require GitHub CI.
- Routed read-only QA reports no remaining blocking, high or medium finding after retry-safe
  deletion, project-row upload locking, bounded full-image decoding and reactive media-form fixes.
- PORT-007 changes no public route or React/Laravel frontend boundary. Lovable contains no
  authentication/edit/settings reference state, so the private CMS reuses semantic tokens without
  claiming visual parity; public `/experience`, `/education`, `/skills` and `/certifications`
  composition remains assigned to PORT-008.
- Docker Compose remains intentionally absent from the active V3 baseline; PostgreSQL behavior
  is verified by CI.
- PR #27 merged PORT-006 into `release/v3.0.0/main`; issue #11 is closed as completed.
- Next gate: publish the PORT-007 draft PR and verify every CI/migration gate.
- After PORT-007 merge, issue #13 / PORT-008 is the next queue item for a fresh `next issue` chat.
