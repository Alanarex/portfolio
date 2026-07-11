# Blender → Three.js Handoff Contract

## Purpose

Keep Blender work and web integration synchronized without relying on chat memory.

## Required deliverables

```text
scene-vNNN.glb
poster-vNNN.webp
fallback-mobile-vNNN.webp
manifest-vNNN.json
preview-vNNN.png
```

Blender source files remain private or use Git LFS when explicitly approved.

## Required node names

- `avatar`
- `desk`
- `main_monitor`
- `secondary_monitor`
- `laptop`
- `bookshelf`
- `certificates`
- `calendar`
- `kanban_board`
- `server_rack`
- `phone`
- `airplane_model`
- `car_model`
- `fishing_object`

## Required avatar animation clips

- `IdleCoding`
- `LookAtUser`
- `Greeting`
- `ReturnToCoding`

## Coordinate and export rules

- glTF/GLB 2.0;
- meters as scene scale;
- Y up at runtime export;
- transforms applied;
- no missing external textures;
- simple PBR materials;
- limited material count;
- no cloth, hair or physics simulation;
- no autoplay audio;
- hotspot nodes must remain independently addressable.

## Performance targets

Targets are provisional until device testing:

- preview payload target: <= 4 MB;
- total fullscreen assets target: <= 12 MB;
- texture maximum: 2048 px only when justified, otherwise 1024 px or lower;
- low-to-medium triangle count;
- baked lighting preferred;
- minimal real-time shadows;
- no heavy post-processing in v1 of the scene.

## Manifest fields

```json
{
  "sceneVersion": "1.0.0",
  "glb": "scene-v001.glb",
  "poster": "poster-v001.webp",
  "mobileFallback": "fallback-mobile-v001.webp",
  "nodes": [],
  "animations": [],
  "triangleCount": 0,
  "compressedBytes": 0,
  "sha256": "",
  "notes": ""
}
```

## Validation owner

- Blender owner validates modeling, rigging and export.
- Three.js worker validates node discovery, animation playback, hotspot mapping and performance.
- QA reviewer validates fallback behavior, fullscreen flow, keyboard operation and regression risk.
- Orchestrator accepts or rejects the asset version.
