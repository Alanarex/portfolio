# ADR-002: SSR Public Portfolio with Isolated Interactive Frontend Modules

## Status

Accepted

## Context

The public portfolio needs SEO, accessibility and a fast first render. The private dashboard needs a richer application interface. The 3D section requires Three.js but must not dictate the whole frontend stack.

## Decision

Use:

- Laravel Blade/server-rendered HTML for the public landing page, case studies and essential portfolio content;
- Vue 3 + TypeScript with Inertia for the private dashboard and rich authenticated interactions;
- vanilla Three.js + TypeScript mounted as an isolated, lazy-loaded public-page module;
- Vite for bundling, code splitting and shared frontend assets;
- shared design tokens across Blade, Vue and Three.js overlays.

The existing React/TypeScript prototype must be audited before useful UI or Three.js work is migrated. React is not retained as a platform dependency unless PORT-000 identifies a specific isolated component whose migration cost is unjustified.

## Consequences

- public content remains indexable and accessible;
- the dashboard keeps a component-based frontend without requiring a separate SPA deployment;
- Three.js loads independently and can fail without breaking core content;
- shared design tokens and API contracts must be maintained across Blade and Vue;
- the React prototype will be classified and migrated deliberately rather than deleted blindly.
