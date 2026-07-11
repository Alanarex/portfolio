# ADR-002: SSR Public Portfolio with Isolated Interactive Frontend Modules

## Status

Proposed — requires final confirmation before scaffolding

## Context

The public portfolio needs SEO, accessibility and fast first render. The dashboard needs a richer application interface. The 3D section requires Three.js but should not dictate the whole frontend stack.

## Decision

Recommended approach:

- Laravel SSR/Blade for the public landing page and case-study content;
- Vue 3 + TypeScript for dashboard-rich interactions, either through Inertia or a clearly isolated SPA area;
- vanilla Three.js + TypeScript mounted as an isolated public-page module;
- Vite for bundling and code splitting.

## Consequences

- public content remains indexable;
- Three.js loads independently;
- dashboard can use a component architecture;
- the project must define shared design tokens across Blade and Vue.
