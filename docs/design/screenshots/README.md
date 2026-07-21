# Lovable screenshot reference

## Completed capture

Captured from the authenticated, time-limited Lovable preview on 2026-07-21. The canonical
reference contains every reachable public route in both designed themes and all four audience
modes: `general`, `recruiter`, `client`, and `technical`.

- 15 routes × 2 themes × 4 audience modes = **120 full-page JPEGs**.
- Theme is explicitly selected and verified before every image (`dark` class absent/present).
- Audience selector value is verified before every image.
- Browser viewport remained fixed at 1363 × 954 CSS pixels. Full-page JPEG widths are 1348 px
  on scrolling pages and 1363 px on pages without a vertical scrollbar.
- All files are non-empty, readable JPEGs. Heights range from 936 px to 3850 px.
- Eight byte-identical image groups were detected. These are retained intentionally: they show
  routes whose rendered result is not audience-sensitive and prove that each matrix cell was
  checked rather than inferred.

## Directory convention

```text
docs/design/screenshots/
├── light/{general,recruiter,client,technical}/<route>.jpg
└── dark/{general,recruiter,client,technical}/<route>.jpg
```

For example, `/skills` in recruiter mode is:

- `light/recruiter/skills.jpg`
- `dark/recruiter/skills.jpg`

## Route map

The filename below exists under every `<theme>/<audience>/` directory.

| Route | Stable filename | State |
|---|---|---|
| `/` | `feed.jpg` | Default feed |
| `/highlights` | `highlights.jpg` | Highlighted posts |
| `/projects` | `projects.jpg` | Project index |
| `/projects/portfolio-platform` | `project-portfolio-platform.jpg` | Project detail |
| `/projects/sprint-radar` | `project-sprint-radar.jpg` | Project detail |
| `/projects/ledgerly` | `project-ledgerly.jpg` | Project detail |
| `/experience` | `experience.jpg` | Experience timeline |
| `/education` | `education.jpg` | Education timeline |
| `/skills` | `skills.jpg` | Skills Explorer |
| `/certifications` | `certifications.jpg` | Certification index |
| `/media` | `media.jpg` | Media index |
| `/activity` | `activity.jpg` | Development activity |
| `/analytics` | `analytics.jpg` | Public analytics |
| `/contact` | `contact.jpg` | Contact form |
| `/profile` | `profile.jpg` | Profile/about |

## States not represented as standalone pages

- Application authentication, edit, and settings routes are absent from the prototype route tree.
- Empty and loading states are transient/component states with no stable public trigger in this
  seeded preview; they must not be invented from unrelated screens.
- First-visit splash/onboarding was already consumed in this preview session and has no public
  reset control.
- The preview host intercepts unknown paths, so it does not expose a stable application 404 page.
- The Lovable editor/login/badge surfaces are platform chrome and are not application references.

## Agent usage

Before changing a public UI route, inspect the matching light and dark images for the relevant
audience modes. Compare layout, hierarchy, spacing, typography, colors, responsive rails, and
component states. Re-check both themes after implementation. Do not copy the placeholder identity
“Alex Karim”; production content must remain CMS/configuration-backed.
