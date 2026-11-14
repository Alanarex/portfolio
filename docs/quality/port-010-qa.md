# PORT-010 QA — Interactive 3D room placeholder

Scope: issue #15. Placeholder only: procedural low-poly geometry, no GLB, no texture, no audio.

## Architecture

- `Modules/Portfolio3D` owns configuration (`scene_configurations`, `scene_hotspots`), the admin
  form (`/dashboard/scene`, administrator-only, audited), the cached `PublicSceneReader` and the
  `/{locale}/scene.json` manifest. Scene is **disabled until enabled** by the administrator.
- Runtime: `resources/js/room-scene/` (vanilla Three.js + TypeScript).
  - `index.ts` is the only file in the public entry; it never imports Three.js.
  - `room.ts` (+ `build-room.ts`, `avatar-machine.ts`) is a lazy chunk loaded when the section
    approaches the viewport (300 px margin) or on explicit request.
  - `avatar-machine.ts` is the DOM-free state machine
    `LOADING → IDLE_CODING → TURNING_TO_USER → GREETING → RETURNING_TO_CODING`.
- Every hotspot is a real link in the page (`nav.room__links`), also rebuilt inside the fullscreen
  dialog. Hotspot targets are an allowlist of landing sections; a target that is not rendered
  (activity disabled) is omitted from both the links and the manifest.

## Fallback matrix

| Condition | Behaviour |
|---|---|
| No JavaScript | Stage hidden, link list visible |
| Coarse pointer / narrow viewport / Save-Data / `low-power` | Poster (or mobile fallback image) + explicit "Load" button, no Three.js download |
| `prefers-reduced-motion` or admin `always` | Loads normally, render-on-demand (no continuous loop), instant camera and avatar poses |
| WebGL unavailable or context lost | `data-state="unavailable"`, message, link list unchanged |
| Skip control | Disposes renderer/canvas, back to poster with a Load button |

Performance controls: render loop paused when the stage is outside the viewport or the tab is
hidden; DPR clamped to 1.5 (1.0 in low-power); 30 FPS cap and no antialiasing in low-power;
no shadows, no post-processing; shared unit geometries and one material per colour.

## Measured budgets (production build)

| Item | Size |
|---|---|
| `room-*.js` (room runtime, lazy) | 15.1 kB raw / 5.9 kB gzip |
| `three.module-*.js` (shared with the career-road preview, lazy) | 569.7 kB raw / 143.2 kB gzip |
| Inline scene payload (JS only, no assets) | ≈ 0.58 MB raw (target ≤ 4 MB) |
| Fullscreen additional assets | 0 (same canvas moved into the dialog; target ≤ 12 MB) |
| Landing initial load | no `room-*`/`three.module-*` request before the section approaches the viewport (asserted) |

Frame rate on real mid-range mobile devices is **not** measured here (CI uses software WebGL);
this must be validated on devices before PORT-011.

## Validation commands

```bash
php artisan test                      # PHPUnit (SceneConfigurationTest: 14 tests)
vendor/bin/pint --test && vendor/bin/phpstan analyse
npm run lint && npm run stylelint && npm run build
npx playwright test                   # room-scene.spec.ts + existing public flows
```
