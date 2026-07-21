# V3 development handoff

## Start here

```bash
git clone --branch release/v3.0.0/main --single-branch https://github.com/Alanarex/portfolio.git
cd portfolio
cp .env.example .env
composer install
npm ci
php artisan key:generate
.ai-dev/bin/verify
```

Use the environment-specific Docker workflow when it is reintroduced; the current branch is a
clean Laravel baseline and must not claim Docker readiness until the files and checks exist.

## Source-of-truth order

1. Assigned GitHub issue and acceptance criteria.
2. `docs/product/v3-social-portfolio-spec.md`.
3. Architecture decisions under `docs/architecture/decisions/`.
4. `docs/design/lovable-reference.md` for interaction and visual intent.
5. Existing content documents under `docs/content/`.

## First implementation slice

1. Create a bounded issue/branch for the V3 UI foundation.
2. Add Laravel layout, semantic tokens, theme persistence, locale routing, and brand assets.
3. Implement splash and audience onboarding with accessible skip/reduced-motion behavior.
4. Implement the responsive shell and mocked feed through contracts that can later consume APIs.
5. Add feature/component/E2E checks and capture the full screenshot matrix.
6. Replace mock content with CMS-backed profile/feed models in a separate slice.

## Definition of ready

- Product and visual reference read.
- Route, audience, language, theme, and viewport acceptance states identified.
- No dependency on the private Lovable runtime.
- No placeholder identity copied from the prototype.
- Test and capture plan attached to the issue.

## Known blockers

- The shared Lovable preview currently requires authentication, so full-page reference
  screenshots cannot be captured from the public URL.
- The private TanStack repository remains a prototype source. Production code should be ported
  feature-by-feature into Laravel rather than replacing the Laravel root with a second framework.
