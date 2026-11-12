# ADR-008: Red interface redesign and car scene

## Status

Accepted (owner request, PR #52).

## Context

ADR-007 set neutral navy/slate surfaces, one warm accent and system-first typography as autonomous defaults. The owner rejected the resulting interface as generic and explicitly requested a red palette, richer motion, a speedometer loader and a 3D car on a road.

## Decision

- Owner choices override ADR-007 visual defaults: red palette, self-hosted variable fonts, scroll-driven motion, Lenis smooth scroll.
- Accessibility rules remain: visible focus, reduced-motion fallbacks, contrast-checked accent (`#ba181b` with white text), HTML-first public content.
- The 3D scene stays isolated and lazy-loaded with poster, coarse-pointer on-demand, reduced-motion and WebGL-failure fallbacks.
- The car is a CC BY 4.0 third-party model (RES1N), optimized and credited. It replaces the generic avatar of ADR-007 for v1 by owner request; its manufacturer badges are kept by owner choice and the brand is not named in site copy beyond the attribution.

## Consequences

- Dependency added: `lenis`, three fontsource packages.
- The attribution line must stay visible while the model is shipped.
- Masking the badges later requires editing the source mesh.
