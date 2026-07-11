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
