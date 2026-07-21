# Lovable code integration

## Source

- Repository: `Alanarex/profile-nexus-58`
- Imported revision: `321da07f85c62914ff029fdca2e1e047cd8ca2b7`
- Original runtime: TanStack Start, React 19, Tailwind CSS 4
- Laravel adaptation: client-side TanStack Router mounted by Laravel Vite

## Production boundary

Laravel remains responsible for HTTP delivery, persistence, authentication, APIs,
queues, caching, integrations, SEO responses, and the private dashboard. The React
application under `resources/js/portfolio/` owns the interactive public interface.

The catch-all route in `routes/web.php` serves `resources/views/portfolio.blade.php`.
Vite mounts `resources/js/app.tsx`, then TanStack Router renders the imported route tree.

## Imported UI

- responsive three-column application shell and mobile navigation
- feed and post cards
- profile, projects, project detail, skills, experience, education, certifications,
  media, activity, analytics, contact, and highlights routes
- light/dark preference, French/English preference, and four audience modes
- splash, welcome flow, command palette, footer, and contextual sidebars
- Lovable design tokens, animations, and semantic Tailwind utilities

## Deliberate adaptations

- removed Lovable-specific SSR shell, scripts, and error-reporting hooks
- replaced the placeholder portrait and logos with supplied brand assets
- replaced the placeholder profile identity with verified project profile data
- removed fake external contact destinations
- kept Laravel as the server/runtime and added React to its existing Vite build

## Transitional data warning

`resources/js/portfolio/data/portfolio.ts` still contains prototype fixture entities.
They are useful for visual parity only and must not ship as factual portfolio content.
Replace this module behind typed Laravel API repositories, feature by feature. Do not
copy fixture claims, employers, metrics, certifications, testimonials, or projects into
persistent data.

## Verification

Run:

```bash
npm ci
npm run build
php artisan test
```

Compare affected routes against `docs/design/screenshots/README.md` in both themes and
every relevant audience mode. The imported code is the behavioral starting point; the
screenshots remain the visual reference.
