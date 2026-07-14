# portfolio-platform-v2

A bilingual, social-feed-style developer portfolio and private content-management platform that presents verified career events, skills, projects, case studies, Git activity, contact options, downloadable CV assets, and an optional accessible 3D experience while allowing a single administrator to control all public content.

- Type: web-application
- Domain: developer-portfolio-content-management
- Lifecycle: existing

## Users

- public visitor
- recruiter or hiring manager
- prospective client or collaborator
- authenticated single administrator

## Goals

- Present the developer's professional identity, experience, education, certifications, languages, skills, projects, achievements, and interests in a modern portfolio
- Use a social-media-style chronological feed rather than a traditional article-based personal blog
- Support French and English, with French as the default public locale
- Automate the creation and updating of portfolio events from verified GitHub, GitLab, and LinkedIn activity where platform permissions allow it
- Give the administrator full control over profile data, settings, publication status, ordering, visibility, media, projects, case studies, CV versions, and integrations
- Provide a dedicated interactive Skills Explorer while also associating skills with relevant feed events and projects
- Make project repositories, case studies, screenshots, demos, and optional subdomain demonstrations easy to discover without exposing private source code
- Offer secure contact options through a form, configured social links, email visibility controls, and a WhatsApp contact link
- Achieve strong accessibility, technical SEO, performance, privacy, observability, automated testing, and deployment readiness
- Provide an optional low-poly Three.js portfolio room that enhances the experience without becoming required for access to essential content
- Establish reusable foundations for later project activation, deployment assistance, reusable module catalogs, task tracking, Kanban, calendar, and delivery statistics

## Features

- French and English localized public content with French as the default locale
- Dark and light themes using the supplied theme-specific logos and personal profile photo
- Responsive public site with semantic server-rendered Blade content and progressive enhancement
- Social-media-style event feed for professional experiences, education, certifications, projects, releases, milestones, achievements, and personal interests
- Latest-date sorting by default with an optional relevance-based sort
- Pagination or incremental loading so the complete feed is never loaded at once
- Redis-backed caching and cache invalidation for public content, integrations, settings, and feed queries
- Daily scheduled GitHub synchronization that creates or updates verified posts without duplicates
- GitLab activity synchronization and public contribution-grid support with private activity excluded
- LinkedIn activity import or synchronization when permitted by LinkedIn APIs and account permissions
- Default images for every event type with event-specific media preferred when verified and available
- Profile, professional titles, positioning, summary, biography, location, availability, social links, and visibility settings
- Career experience management with quantified achievements restricted to verified values
- Education, certification, language-proficiency, and publication-status management
- Skills taxonomy with categories, icons, ordering, visibility, related projects, related events, and a dedicated Skills Explorer
- Project and case-study management with stable slugs, role, dates, technologies, architecture, contribution, results, metrics, lessons, repository visibility, demo links, and featured ordering
- Media management for images, screenshots, posters, documents, CV files, social previews, and 3D assets with alt text and metadata
- Hidden authenticated dashboard with CRUD, filters, ordering, publication controls, preview, empty states, and audit events
- Project enable and disable controls plus public feature flags for projects and demonstrations
- Public landing page, project listing, project case-study pages, section navigation, and accessible empty states
- GitHub and GitLab activity presentation with placeholders or cached fallback data when APIs are unavailable
- Secure contact form with validation, CSRF protection, accessible anti-spam controls, rate limiting, and queued email delivery
- Configurable email, phone, location, social, WhatsApp, activity, CV, and contact visibility controls
- Versioned downloadable CV delivery enabled only when a verified published file exists
- Technical SEO with canonical URLs, metadata, Open Graph, social previews, sitemap, robots configuration, and schema.org structured data
- Accessible 404 and 500 error pages without internal information disclosure
- Privacy-conscious analytics abstraction, SEO indexing monitoring, and conversion-event tracking without invasive profiling
- Structured logs, request identifiers, liveness checks, readiness checks, queue and scheduler monitoring, and error tracking
- Optional lazy-loaded Three.js portfolio room with fullscreen presentation, hotspots, avatar states, posters, mobile fallback, reduced-motion behavior, and HTML equivalents
- Later-phase reusable Laravel or frontend module catalog, project setup assistance, deployment assistance, Kanban, task manager, calendar, and project statistics

## Non-goals

- Native mobile applications in the first release
- Public registration, community accounts, or a multi-user administration system in the first release
- E-commerce, payments, newsletter management, CRM, or invasive advertising and tracking
- Publishing unverified achievements, metrics, private repositories, confidential client data, or private source code
- Requiring JavaScript, WebGL, animations, or the 3D scene to access essential portfolio content
- Heavy 3D physics, audio, complex post-processing, dynamic hair, cloth simulation, or a dashboard geometry editor
- Fully automatic production deployment or merging without verification and explicit production approval
- Complete client-project licensing, remote activation enforcement, and project-generation automation in the initial public portfolio release

## Success criteria

- Essential public portfolio content renders correctly without JavaScript or WebGL
- French is the default locale and every published public page can be delivered consistently in French and English
- The administrator can create, edit, reorder, preview, publish, unpublish, and archive all supported content types from the private dashboard
- Only explicitly published and public fields, repositories, media, metrics, and activity are exposed to visitors
- Daily integration jobs are idempotent, rate-limit aware, observable, retryable, and do not create duplicate feed events
- Visitors can browse a paginated feed sorted by latest date by default and switch to a relevance-based ordering
- Projects have stable SEO-ready slugs and can expose approved case studies, repository links, media, and demos without leaking private data
- Contact submissions are validated, rate-limited, protected against spam and header injection, and delivered through a queue
- CV downloads serve only the selected verified published version and remain disabled when no valid file exists
- Keyboard navigation, focus order, landmarks, headings, forms, dialogs, themes, reduced motion, and screen-reader output meet WCAG 2.2 AA expectations
- Key public pages pass Core Web Vitals targets and remain responsive under representative mobile and desktop conditions
- Three.js loads only when needed, pauses offscreen, respects reduced motion, provides fallbacks, and never blocks essential content
- PHPUnit, static analysis, PHP formatting, JavaScript linting, CSS linting, frontend builds, container validation, end-to-end tests, accessibility tests, and security-critical tests pass in CI
- Migrations roll back cleanly and production changes require a verified release process and manual approval
- Health checks, structured logs, error tracking, analytics, and indexing monitoring provide enough evidence to diagnose failures and measure public-site quality

## Product languages

- French
- English

## Constraints

- French must remain the default locale
- Public content must be responsive, keyboard accessible, and compatible with reduced-motion preferences
- Essential information must remain available without JavaScript, WebGL, the 3D scene, or external integration APIs
- The administration boundary is private and limited to a single administrator unless a later approved requirement changes it
- Public registration and public password-reset routes must remain disabled
- Exact address and phone details are hidden by default and public email is shown only when explicitly configured
- Private repositories, confidential client data, credentials, source code, unpublished content, and unverified metrics must never be exposed
- Integration synchronization must be idempotent, cached, rate-limit aware, retryable, and safe when providers are unavailable
- Feed queries must use pagination or incremental loading and must not load all posts in one response
- Caching must have deterministic invalidation after dashboard updates and external synchronization
- Uploaded files and media require MIME, extension, size, authorization, alt-text, provenance, and storage validation
- Third-party visual and 3D assets require documented licensing and provenance
- Supplied profile photography and light, dark, black, and white logo variants should be reused appropriately
- The optional 3D experience must be isolated, lazy-loaded, performance-budgeted, and backed by static HTML and image fallbacks
- Docker Compose is the canonical local environment and production must remain Docker-compatible
- Repository modules must preserve clear ownership of routes, views, migrations, configuration, commands, policies, and tests
- Secrets and production credentials must never be committed or persisted by automation
- GDPR-compliant personal-data handling and minimal contact-message retention are required
