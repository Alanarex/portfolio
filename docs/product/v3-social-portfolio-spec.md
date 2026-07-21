# Portfolio V3 — product specification

## Product statement

Portfolio V3 is a personal professional social network for Alaa Khalil. Its primary surface
is a short, visual, chronological feed rather than a blog, a traditional CV, or a collection
of long-form articles.

## Audiences

The first visit asks why the visitor is here and stores one of four modes locally:

- recruiter: experience, education, responsibility, outcomes, and measurable results;
- client: client work, demos, services, availability, and business benefits;
- technical: repositories, architecture, releases, CI/CD, tests, Docker, and monitoring;
- unpersonalized: balanced presentation.

The selected mode remains editable and changes wording and contextual content without
changing the underlying event.

## Global requirements

- French is the default language; English is fully supported.
- Light and dark themes are persistent and intentionally designed.
- Language changes preserve route, filters, audience, post, and scroll position.
- Desktop uses left profile navigation, central feed, and contextual right rail.
- Mobile uses bottom navigation and a discreet floating WhatsApp action.
- Global search is available with `Ctrl+K`.
- The supplied portrait and theme-aware logos are mandatory.

## Core routes

| Route concept | Purpose |
|---|---|
| Feed | Recent-first social activity with audience-aware copy, filters, and alternate sorts |
| Highlights | Curated work and achievements |
| Projects | Visual project grid and project social profiles |
| Experience | Animated career timeline linked to posts and projects |
| Education | Education timeline, documents, projects, and skills |
| Skills Explorer | Topic-style discovery grid; selecting a skill filters the feed |
| Certifications | Credentials and verification links |
| Media | Images, galleries, videos, and demos |
| Activity | GitHub/GitLab activity, releases, deployments, and heatmaps |
| Statistics | Portfolio and engineering metrics |
| Contact | Conversation-style contact with WhatsApp as the primary channel |

Public URLs use `/fr` and `/en`; French is also the `x-default` SEO target.

## Feed contract

Posts are short and scannable. A post may include media, metrics, tags, technologies, a
linked project/repository/demo, reactions, comments, views, share, and bookmark actions.
Do not add reading time, table of contents, “read article” calls to action, or long blocks.

Each event supports six editorial variants: recruiter/client/technical in French and English.
Default sort is newest. Other sorts are recommended, most viewed, and most commented.
Initial loading returns 8–12 items using cursor pagination, with progressive prefetch and an
accessible “load more” fallback.

## Event automation

GitHub and GitLab use webhooks for real-time events and a daily reconciliation job. Track
repositories, pushes, PR/MR lifecycle, closed issues, releases, deployments, workflows, and
milestones. Do not publish every commit; aggregate low-value activity into useful summaries.

The dashboard remains the source of truth for LinkedIn. Support manual URL import, draft
generation, cross-post status, and the official API when authorization exists. Do not scrape.

## Media selection

Use this priority: manual media, event attachment, project cover, release/demo screenshot,
repository social image, legally available LinkedIn media, organization logo, generated
preview, then an event-type default. Provide thumbnail/feed/detail/Open Graph variants using
AVIF or WebP with fallback.

## Contact

Use a conversational interface with recruitment, freelance project, collaboration, technical
question, and other intents. Display availability, average response time, timezone, spoken
languages, and accepted missions. Channels are WhatsApp, email, LinkedIn, and scheduling.

## Non-functional requirements

- Redis-backed caching with explicit invalidation for content and external synchronization.
- Structured logs without secrets or sensitive complete payloads.
- Analytics for audiences, content, demos, CV, WhatsApp, repositories, and conversions.
- Localized metadata, canonical/hreflang, Open Graph, structured data, sitemap, and robots.
- Keyboard navigation, visible focus, screen reader support, reduced motion, and Lighthouse
  accessibility target of at least 95 on audited public pages.
- Docker/Compose, Nginx, local/staging/production environments, CI/CD, health checks,
  security scanning, tests, coverage, monitoring, and Core Web Vitals.

## Target architecture

Laravel 13 / PHP 8.3+, MySQL or MariaDB, Redis, queues, and scheduler. The frontend uses
Vite, Tailwind CSS, Font Awesome for general icons, and official technology icons where
available. React/Motion may be introduced only where it materially improves the experience.

Initial modules: Auth, Portfolio, Feed/Activity, Projects, Demos, Skills, Career, Media,
Settings, and ActivityLog. Later modules include Tasks, Kanban, Calendar, GitHub, GitLab,
Statistics, Monitoring, registry/factory/deployment/licensing/infrastructure, and MobileApi.

## Quality gates

Pint, PHPStan/Larastan (start 6, target 8), PHPUnit or Pest, ESLint, Stylelint, component and
E2E tests where useful. Global coverage target is 75%; Domain/Application target is 90%.
All lint, analysis, tests, builds, security checks, staging smoke tests, and health checks must
pass before production deployment.
