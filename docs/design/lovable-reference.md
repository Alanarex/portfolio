# Lovable visual reference

## Provenance

- Prototype repository: `Alanarex/profile-nexus-58` (private)
- Reference commit: `321da07f85c62914ff029fdca2e1e047cd8ca2b7`
- Prototype stack: TanStack Start, React 19, TypeScript, Tailwind CSS 4
- Preview requested: `https://preview--profile-nexus-58.lovable.app/`
- Inspection result on 2026-07-21: the preview redirects unauthenticated browsers to Lovable login.

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

## Capture matrix

Each route must be captured after the Laravel implementation is runnable. Store captures under
`docs/captures/lovable/` using `{route}-{language}-{theme}-{viewport}.png`.

Required dimensions:

| State | Values |
|---|---|
| Language | `fr`, `en` |
| Theme | `light`, `dark` |
| Viewport | desktop 1440×1024, mobile 390×844 |
| Audience-sensitive pages | recruiter, client, technical, general |

Minimum routes: splash, welcome, feed, profile, projects, project detail, skills, experience,
education, certifications, media, activity, analytics, contact, and highlights.

Do not commit a Lovable login screenshot as a design reference. Update this file with the
exact capture command and verified route list when the local capture pass is complete.

## Brand assets

| File | Intended use |
|---|---|
| `docs/assets/brand/portrait.png` | Profile portrait and profile cards |
| `docs/assets/brand/logo-black.png` | Monochrome mark on very light backgrounds |
| `docs/assets/brand/logo-light.png` | Primary light-theme mark |
| `docs/assets/brand/logo-dark.png` | Dark-theme mark with electric-blue accent |
| `docs/assets/brand/logo-white.png` | White mark on dark imagery/surfaces |

Keep originals lossless. Generate runtime-responsive derivatives during implementation.
