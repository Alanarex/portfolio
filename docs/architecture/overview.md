# Architecture overview

Detected adapters: composer, laravel, npm, docker.

Deployment target: OVH VPS in the European Union using Docker Compose, Nginx, HTTPS, PostgreSQL, Redis, queue workers, and the Laravel scheduler.

## Technical configuration

```yaml
applicationType: full-stack-web
architecture: modular-monolith
runtime: PHP 8.3+ with PHP 8.4 CI and Node.js 22
packageManager: Composer 2 and npm
languages:
  - PHP
  - TypeScript
  - JavaScript
  - HTML and Blade templates
  - CSS
frameworks:
  - Laravel 13
  - Blade SSR
  - Inertia.js
  - Vue 3
  - Three.js
  - Vite
databases:
  - PostgreSQL 17
cache:
  - Redis 7.4 for cache, sessions, queues, synchronization state, and public
    read models
messaging:
  - Laravel queues backed by Redis
  - Scheduled Laravel jobs for external synchronization and maintenance
search:
  - PostgreSQL-backed filtering and relevance ranking for feed events, projects,
    and skills
storage:
  - Laravel filesystem abstraction for public and private media, CV files,
    screenshots, posters, and 3D assets
  - S3-compatible object storage support for future production scaling
infrastructure:
  - Docker Compose
  - Nginx 1.27
  - PHP-FPM
  - PostgreSQL 17
  - Redis 7.4
  - Laravel queue worker
  - Laravel scheduler
  - Mailpit for local email testing
  - OVH VPS
  - GitHub Actions
integrations:
  - GitHub API for repository, release, contribution, and activity
    synchronization
  - GitLab API for project and contribution activity
  - LinkedIn import or API integration subject to platform permissions
  - Transactional email provider for production contact delivery
  - WhatsApp deep links for direct contact
  - Privacy-conscious analytics provider configured only after explicit selection
  - Search-engine indexing and webmaster monitoring tools
  - Error-tracking provider such as Sentry
```

## Quality configuration

```yaml
testTypes:
  - unit
  - feature
  - integration
  - end-to-end
  - accessibility
  - security
  - upload validation
  - queue and scheduler
  - migration rollback
  - container smoke
  - visual regression
  - performance
testFrameworks:
  - PHPUnit 12
  - Laravel application testing utilities
  - Playwright
  - automated accessibility assertions
  - Lighthouse or equivalent performance audits
coverageTarget: 80
linting:
  - Larastan and PHPStan
  - ESLint
  - Stylelint
formatting:
  - Laravel Pint
typeChecking: true
accessibilityTarget: WCAG 2.2 AA
performanceTargets:
  - Pass Core Web Vitals on key public pages under representative mobile and
    desktop conditions
  - Render essential public content through Blade SSR without requiring
    JavaScript
  - Paginate or incrementally load the social feed and project listings
  - Cache stable public read models and external integration responses in Redis
  - Use responsive images, explicit dimensions, alt text, lazy loading, and
    modern formats where appropriate
  - Lazy-load Three.js only near or after explicit interaction and provide
    static fallbacks
  - Pause 3D rendering outside the viewport, clamp device pixel ratio, and
    document bundle, payload, triangle, and frame-rate budgets
```

## Security configuration

```yaml
authentication: Single-administrator email and password authentication with
  secure server-side sessions, session regeneration, no remember-me persistence,
  no public registration, and interactive credential provisioning and rotation
authorization: Module-level policies and explicit public or private visibility
  rules for every administrative action, field, repository, media asset, CV
  version, preview, and integration
compliance:
  - GDPR
  - Privacy by default
secretManagement: Environment variables and deployment or GitHub secrets outside
  version control, with no credentials persisted in logs, commands, generated
  prompts, synchronization state, or repository files
dataSensitivity: Administrator credentials, contact submissions, private
  repository metadata, unpublished portfolio content, private media and CV
  versions, integration tokens, analytics data, and client-project information
requirements:
  - Rate-limit authentication and contact endpoints using normalized identifiers
    and generic failure responses
  - Use CSRF protection, secure cookies, HTTP-only sessions, appropriate
    SameSite settings, and HTTPS in production
  - Revoke active sessions after administrative password reset and prevent
    credentials from leaking through shell history or process arguments
  - Record structured authentication and privileged-action audit events without
    passwords, tokens, session identifiers, or raw private email addresses
  - Validate upload MIME type, extension, size, authorization, storage path,
    image metadata, alt text, and asset provenance
  - Prevent insecure direct object references and enforce public, private,
    draft, preview, and published boundaries
  - Prevent email header injection, spam abuse, sensitive-data exposure, and
    uncontrolled contact-message retention
  - Never expose private GitHub or GitLab activity, repository details, client
    information, source code, or unverified metrics
  - Sanitize and validate external integration payloads and handle API outages,
    retries, rate limits, and duplicate events safely
  - Keep error pages and production logs free of stack traces, secrets,
    credentials, and personal data
  - Require explicit approval for production-risk operations, deployment,
    destructive changes, and external credential configuration
```

## Delivery configuration

```yaml
environments:
  - local
  - staging
  - production
ci: GitHub Actions with backend, frontend, container, test, static-analysis,
  lint, accessibility, and build quality gates
cd: Verified release pipeline with manual production approval, database
  migration checks, health verification, rollback readiness, and no autonomous
  production deployment
hosting: OVH VPS with Docker Compose, Nginx, PHP-FPM, PostgreSQL, Redis, queue
  workers, scheduler, HTTPS, and external backups
regions:
  - eu-west
  - France and European Union data residency preferred
containerized: true
monitoring:
  - Structured JSON application and authentication logs
  - Request-ID propagation
  - Liveness endpoint at /up
  - PostgreSQL and Redis readiness endpoint at /ready
  - Error tracking such as Sentry
  - Uptime and HTTP health monitoring
  - Queue worker and scheduler health monitoring
  - Integration synchronization status, failures, retries, and rate-limit
    monitoring
  - Core Web Vitals and public performance monitoring
analytics:
  - Privacy-conscious public-site analytics with no vendor enabled by default
  - Search-engine indexing, sitemap, robots, canonical, and webmaster-console
    monitoring
  - Anonymous page, project, CV-download, outbound-link, WhatsApp, and
    contact-conversion events when consent and configuration permit
  - No cross-site profiling, sale of personal data, or invasive fingerprinting
```

## Repository configuration

```yaml
defaultBranch: main
license: MIT
commitConvention: Conventional Commits
pullRequestStrategy: One feature branch and draft pull request per PORT task,
  mandatory CI and specialist review, screenshots for visual work, squash merge
  only after all acceptance criteria pass, then issue closure and roadmap
  reconciliation
issueTracker: GitHub Issues using PORT milestones and the sequential roadmap controller
monorepo: false
```
