# Lovable visual reference

## Provenance

- Prototype repository: `Alanarex/profile-nexus-58` (private)
- Reference commit: `321da07f85c62914ff029fdca2e1e047cd8ca2b7`
- Prototype stack: TanStack Start, React 19, TypeScript, Tailwind CSS 4
- Preview project: `profile-nexus-58`
- Inspection result on 2026-07-21: an authenticated, time-limited Lovable preview rendered the
  application successfully and supplied the canonical screenshot set.

The prototype is the V3 interaction and visual reference. Its hardcoded placeholder identity
(including “Alex Karim”) is invalid and must be replaced by CMS-backed Alaa Khalil content.

## Implemented prototype surfaces

Feed, profile, projects, project detail, Skills Explorer, experience, education,
certifications, media, activity, analytics, contact, highlights, splash, audience onboarding,
left/right rails, mobile navigation, command palette, post cards, and footer.

## Visual direction

- Deep navy `#1a2540` and electric blue `#2563ff` derived from the supplied logo.
- Dark-first translucent surfaces with subtle glow; clean off-white light theme.
- Space Grotesk headings and Inter body copy.
- Three-column desktop social layout and bottom-navigation mobile layout.
- Persisted audience, language, theme, and first-visit state.
- Motion must respect `prefers-reduced-motion`.

## Visual-reference screenshots

The canonical screenshot inventory and capture status live in
`docs/design/screenshots/README.md`. Theme references belong in
`docs/design/screenshots/light/` and `docs/design/screenshots/dark/` with stable route-derived
filenames. Coding agents must inspect and compare both theme images for the affected route/state
before completing UI work.

The completed reference contains 120 full-page desktop JPEGs: 15 reachable routes in light and
dark mode for general, recruiter, client, and technical audiences. See
`docs/design/screenshots/README.md` for the route map, capture validation, and unavailable states.

The canonical set records the French-default preview. English and mobile captures remain a future
extension and must use the same explicit state-verification procedure if added.

## Brand assets

| File | Intended use |
|---|---|
| `docs/assets/brand/portrait.png` | Profile portrait and profile cards |
| `docs/assets/brand/logo-black.png` | Monochrome mark on very light backgrounds |
| `docs/assets/brand/logo-light.png` | Primary light-theme mark |
| `docs/assets/brand/logo-dark.png` | Dark-theme mark with electric-blue accent |
| `docs/assets/brand/logo-white.png` | White mark on dark imagery/surfaces |

Keep originals lossless. Generate runtime-responsive derivatives during implementation.
