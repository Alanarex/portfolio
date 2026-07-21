# Lovable screenshot reference

## Current result

No application screenshots are present. On 2026-07-21, the preview URL and every tested
application path redirected to Lovable's login bridge before application HTML rendered:

`https://lovable.dev/login?redirect=...project_id=2942fa44-f9a2-4569-bb01-b386034b1fb6...`

This is an authentication blocker, not an application login route or application error state.
The login page is therefore intentionally excluded from the visual reference set.

## Capture contract

- Desktop viewport: 1440 × 1024 CSS pixels.
- Format: full-page JPEG, readable at native dimensions.
- Destinations: `light/` and `dark/`.
- Filename: stable route/state slug, identical in both theme directories.
- Before every capture: set the requested theme explicitly, then verify the rendered theme from
  the current document state. Never rely on a saved preference from a previous route.
- Wait for navigation, `document.fonts.ready`, images, animations, lazy content, and theme
  transitions. Respect reduced-motion behavior where available.
- Validate every pair for dimensions, non-zero size, legibility, duplicates, unexpected redirects,
  missing assets, and error content.

## Route and state inventory

The route list below is derived from prototype commit
`321da07f85c62914ff029fdca2e1e047cd8ca2b7` and its TanStack file routes. Every entry is blocked by
the same preview-level Lovable authentication redirect. Filenames are reserved now so later
captures remain stable.

| Route/state | Light screenshot | Dark screenshot | Status / blocker |
|---|---|---|---|
| `/` — feed/default | `light/home.jpg` | `dark/home.jpg` | Blocked by Lovable login redirect |
| `/profile` | `light/profile.jpg` | `dark/profile.jpg` | Blocked by Lovable login redirect |
| `/projects` | `light/projects.jpg` | `dark/projects.jpg` | Blocked by Lovable login redirect |
| `/projects/:slug` — representative detail | `light/projects-detail.jpg` | `dark/projects-detail.jpg` | Blocked; seeded slug cannot be discovered in rendered UI |
| `/skills` | `light/skills.jpg` | `dark/skills.jpg` | Blocked by Lovable login redirect |
| `/experience` | `light/experience.jpg` | `dark/experience.jpg` | Blocked by Lovable login redirect |
| `/education` | `light/education.jpg` | `dark/education.jpg` | Blocked by Lovable login redirect |
| `/certifications` | `light/certifications.jpg` | `dark/certifications.jpg` | Blocked by Lovable login redirect |
| `/media` | `light/media.jpg` | `dark/media.jpg` | Blocked by Lovable login redirect |
| `/activity` | `light/activity.jpg` | `dark/activity.jpg` | Blocked by Lovable login redirect |
| `/analytics` | `light/analytics.jpg` | `dark/analytics.jpg` | Blocked by Lovable login redirect |
| `/contact` | `light/contact.jpg` | `dark/contact.jpg` | Blocked by Lovable login redirect |
| `/highlights` | `light/highlights.jpg` | `dark/highlights.jpg` | Blocked by Lovable login redirect |
| First visit — splash | `light/splash.jpg` | `dark/splash.jpg` | Client state blocked before render |
| First visit — audience onboarding | `light/audience-onboarding.jpg` | `dark/audience-onboarding.jpg` | Client state blocked before render |
| Empty state — feed/projects/skills as applicable | `light/empty-state.jpg` | `dark/empty-state.jpg` | No public state controls or rendered app access |
| Loading state | `light/loading.jpg` | `dark/loading.jpg` | App bundle never renders before redirect |
| Application 404/not-found | `light/not-found.jpg` | `dark/not-found.jpg` | Preview intercepts unknown paths with login redirect |
| Application error state | `light/error.jpg` | `dark/error.jpg` | No public trigger or rendered app access |
| Authentication state | — | — | No application auth route found; Lovable platform login excluded |
| Edit/settings state | — | — | No corresponding prototype file route discovered at the reference commit |

## Completeness and validation record

- Discovered application file routes: 13 concrete paths plus one dynamic project-detail route.
- Captured application pages: 0.
- Duplicate images: 0.
- Dimension/readability checks: not applicable because no application images were capturable.
- Redirect verification: root and route requests are intercepted before the application renders.
- Missing routes/states: listed explicitly above; edit/settings/auth application routes were not
  present in the prototype route tree found at the pinned commit.

## Unblocking requirement

Provide either a preview that does not require Lovable authentication or a runnable checkout of
the pinned prototype source. Then execute the complete matrix in one browser session, explicitly
verify theme on every page, replace the blocked statuses with image dimensions and capture dates,
and run byte-level and perceptual duplicate checks before committing the JPEG files.
