# 3D Asset Storage and Lifecycle

## Source files

Blender source files are private design assets and should not be publicly served.

Recommended private storage:

```text
storage/app/private/portfolio-3d/source/
├── room-v001.blend
├── avatar-v001.blend
└── textures-source/
```

For large binary sources, use Git LFS or a private asset store rather than normal Git history.

## Runtime assets

Runtime files:

```text
storage/app/public/portfolio-3d/<scene-uuid>/<version>/
├── scene.glb
├── poster.webp
├── fallback-mobile.webp
└── manifest.json
```

Production may serve these through a CDN or object storage.

## Manifest

The manifest records:

- scene version;
- file hashes;
- compressed sizes;
- node names;
- animation names;
- texture formats;
- triangle count;
- bounding box;
- publication timestamp;
- rollback predecessor.

## Lifecycle

```text
SOURCE
→ OPTIMIZED
→ VALIDATED
→ STAGED
→ PUBLISHED
→ SUPERSEDED/ROLLED_BACK
```

## Validation before publication

- GLB parses correctly;
- required nodes exist;
- required animation clips exist;
- file size within budget;
- triangle count within budget;
- textures compressed;
- no external file references;
- poster and mobile fallback present;
- staging smoke test passes.

## Runtime car model (home page road scene)

- Asset: `public/models/amg-gt-roadster.glb` (~2.7 MB, ~196k triangles, meshopt-compressed).
- Source: "Mercedes Amg GT roadster" by RES1N, https://sketchfab.com/3d-models/mercedes-amg-gt-roadster-30255e1d942a455e970e7c776998f695
- Licence: CC BY 4.0 (attribution displayed in the 3D section of the home page). The licence covers the modeler's work, not the Mercedes-Benz / AMG trademarks: the site copy never names the brand.
- Modifications: decimated (gltf-transform + meshoptimizer), textures resized to 512 px WebP, `logo`, `plate`, `tex1`, `tex2`, `tex3` primitives removed, paint/glass/brake/rear-light materials overridden at runtime in `resources/js/three-preview/scene.ts`.
- Known limitation: badge lettering baked into the original geometry at the rear is still visible; masking it requires editing the source mesh.
- The untouched source archive stays outside the repository.
