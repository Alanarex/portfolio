# Design system

## Direction

Portfolio Platform uses one professional dark-first direction: deep navy canvas, slate surfaces,
high-contrast off-white text and one restrained warm amber accent. Typography is system-first and
requires no external font asset or licence.

## Authoritative tokens

`resources/css/tokens.css` is the only token source. `resources/css/app.css` imports it and exposes
semantic classes consumed by Blade public pages, Vue/Inertia dashboard pages and the Three.js
poster/overlay. Do not duplicate the palette or spacing scale in TypeScript.

Token groups cover:

- canvas, surface, text, border, accent, focus, danger and success colours;
- system sans/monospace stacks;
- a 4–80 px spacing scale;
- small, medium and large radii;
- elevation shadows;
- the 72 rem content width and 160 ms interaction transition.

Native CSS custom properties cannot be used inside media-query conditions. The shared mobile
breakpoint is therefore documented and implemented directly at `44rem` in `app.css`.

## Component contract

Blade and Vue use the same semantic CSS contracts: `.button`, `.panel`, `.card`, `.badge`,
`.alert`, `.state`, `.table-wrap`, form controls and layout classes. Components retain native HTML
semantics: buttons remain buttons, errors are programmatically associated with fields, tables use
captions and scoped headers, and empty/loading/error states have appropriate live-region roles.

All interactive controls have a visible cyan `:focus-visible` ring and a minimum target height of
44 px. Disabled/loading controls remain labelled. Reduced-motion preferences remove smooth
scrolling and collapse non-essential animation/transition durations.

## Layouts

- `resources/views/layouts/public.blade.php` owns the public document, skip link, sticky header,
  navigation, main landmark and footer.
- `resources/js/Layouts/DashboardLayout.vue` owns the private header, responsive sidebar,
  authenticated page shell, skip link and logout state.
- the optional Three.js preview consumes the same surface, border, radius and status tokens while
  keeping its HTML fallback intact.

## Usage guardrails

Use semantic tokens rather than raw colours for new UI. Keep public content HTML-first. Do not add
a second theme, third-party UI kit or cross-runtime component abstraction without a scoped issue.
