# Portfolio module

Composes the HTML-first public landing page, project index and project case studies from
allowlisted readers owned by the Profile, Settings, Career, Skills and Projects modules.

The module does not query CMS tables directly. Essential content is rendered by Blade before
JavaScript runs; `resources/js/public.ts` adds only theme persistence and navigation polish.

Public routes are localized under `/fr` and `/en`. French is the x-default route.

Canonical, Open Graph, Twitter and schema.org metadata are rendered server-side. The sitemap
contains only records returned by public readers. `PublicAnalytics` is bound to a null,
non-persisting implementation by default; enabling any future vendor requires a separate
consent/privacy decision.
