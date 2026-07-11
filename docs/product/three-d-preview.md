# Low-Poly Interactive Developer Room

## Product role

The room is an optional visual preview inside the normal portfolio. It is not the source of truth for portfolio content and must not block access to information.

## User flow

```text
Landing page HTML
→ poster/preview container
→ lazy-load scene near viewport
→ inline low-poly preview
→ user activates Enter Fullscreen
→ fullscreen dialog/overlay
→ avatar and hotspot interactions
→ Escape/Close restores focus
```

## Scene content

- low-poly developer room;
- desk and chair;
- main/secondary monitors;
- laptop, keyboard and mouse;
- bookshelf;
- certificates;
- calendar;
- Kanban board;
- server rack;
- phone/contact object;
- project visual elements;
- airplane model;
- car model;
- fishing object;
- stylized avatar based on approved references.

## Avatar behavior

States:

```text
LOADING
IDLE_CODING
TURNING_TO_USER
GREETING
RETURNING_TO_CODING
```

Default: `IDLE_CODING`.

On click/focus activation:

1. stop/reduce typing loop;
2. play `LookAtUser`;
3. play `Greeting`;
4. show accessible HTML speech bubble;
5. after timeout or dismissal, play `ReturnToCoding`;
6. resume idle coding.

First release uses text only. No autoplay voice.

## Hotspots

| Node | Target |
|---|---|
| `main_monitor` | Featured projects |
| `secondary_monitor` | GitHub/GitLab activity |
| `bookshelf` | Skills |
| `certificates` | Education and certifications |
| `calendar` | Experience timeline |
| `kanban_board` | Project delivery skills |
| `server_rack` | DevOps and infrastructure |
| `phone` | Contact |
| `airplane_model` | Aviation |
| `car_model` | Cars |
| `fishing_object` | Fishing |

Every hotspot has an equivalent HTML link outside the canvas.

## Preview mode

- fixed camera;
- limited animation;
- minimal controls;
- restrained hover outlines/tooltips;
- capped device-pixel ratio;
- no heavy post-processing;
- render loop paused outside viewport.

## Fullscreen mode

- opened only by explicit user action;
- accessible dialog/overlay semantics;
- close button and Escape support;
- focus trap and focus restoration;
- constrained camera transitions rather than free game movement;
- no mandatory WASD navigation;
- user can return to the normal page at any time.

## Performance budgets

Initial targets, to be validated on real devices:

- initial landing page must not download Three.js or GLB assets before the section approaches viewport;
- inline scene target payload <= 4 MB;
- total fullscreen assets target <= 12 MB;
- DPR capped, for example 1.5 desktop and 1.0 lower-power mode;
- baked lighting preferred;
- limited material count;
- limited real-time shadows;
- 30 FPS minimum on approved mid-range mobile fallback mode if 3D is enabled;
- 50–60 FPS target on desktop reference device.

## Fallback hierarchy

1. static poster before loading;
2. reduced-motion mode with limited or no avatar animation;
3. low-power/mobile simplified mode;
4. WebGL-failure poster with normal HTML links;
5. manual Skip/Close 3D control.

## Dashboard configuration

Dashboard may control:

- enable/disable scene;
- scene asset version;
- poster and mobile fallback;
- greeting text;
- hotspot labels/descriptions/targets;
- hotspot visibility;
- performance mode;
- publication and rollback.

Dashboard does not edit geometry or animation rigs.

## Asset contract

Follow `docs/coordination/blender-handoff.md` and `docs/architecture/3d-assets.md`.
