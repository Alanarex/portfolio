# Données et stockage

## Stockage privé applicatif

```text
storage/app/private/
├── projects/
│   └── {project_uuid}/
│       ├── documents/
│       ├── specifications/
│       ├── exports/
│       └── deployment/
├── project-management/
│   ├── roadmap/
│   ├── milestones/
│   ├── features/
│   ├── architecture/
│   │   ├── decisions/
│   │   └── diagrams/
│   └── reports/
│       ├── weekly/
│       └── releases/
├── backups/
├── deployment-artifacts/
└── temporary/
```

## Principe

`storage` contient les fichiers générés ou privés.

Le code source des modules ne doit pas être stocké dans `storage`.

Les documents techniques qui accompagnent le code doivent aussi être versionnés :

```text
docs/
├── architecture/
├── adr/
├── features/
├── api/
└── deployment/
```

## 3D assets

Private Blender sources and public optimized runtime assets must be separated. See `docs/architecture/3d-assets.md`.

Do not commit large `.blend`, uncompressed textures or generated GLB versions into normal Git history without an explicit Git LFS policy.
