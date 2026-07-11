# Portfolio Platform

## Vision

Cette application n'est pas un simple portfolio. Elle constitue une plateforme personnelle composée de :

- une page vitrine publique ;
- un dashboard privé ;
- un CMS de contenu ;
- une gestion de projets et de démonstrations ;
- un système de suivi des features et de l'avancement ;
- un espace tâches, Kanban et calendrier ;
- des intégrations GitHub et GitLab ;
- un registre de modules Laravel réutilisables ;
- un centre de déploiement ;
- un système de licences et d'activation de projets clients ;
- un système avancé de logs, statistiques et observabilité ;
- une future API mobile.

## Architecture générale

```text
Personal Platform
│
├── Public Portfolio
├── Private Dashboard
├── Public API
├── Internal API
└── Infrastructure
```

## Principes

1. Commencer par un monolithe modulaire Laravel.
2. Garder des frontières fonctionnelles strictes entre modules.
3. Stocker le code des modules réutilisables dans des repositories Git.
4. Utiliser le dashboard comme catalogue, registre et interface d'orchestration.
5. Versionner les décisions d'architecture et les documents techniques.
6. Construire progressivement les fonctions avancées.

## Documentation

- [Vision produit](docs/product/vision.md)
- [Architecture système](docs/architecture/system-architecture.md)
- [Architecture modulaire](docs/architecture/modular-architecture.md)
- [Modules](docs/architecture/modules.md)
- [Données et stockage](docs/architecture/storage.md)
- [Roadmap](docs/product/roadmap.md)
- [Dashboard](docs/product/dashboard.md)
- [Portfolio public](docs/product/public-portfolio.md)
- [Démos](docs/product/demos.md)
- [Licensing](docs/product/licensing.md)
- [Feature tracking](docs/tracking/feature-tracking.md)
- [Workflow Git](docs/development/git-workflow.md)
- [Qualité et tests](docs/development/quality.md)
- [Conventions](docs/development/conventions.md)
- [CI/CD](docs/operations/ci-cd.md)
- [Environnements](docs/operations/environments.md)
- [Observabilité](docs/operations/observability.md)
- [AGENTS.md](AGENTS.md)
- [Prompt Lovable](prompts/lovable-ui.md)

## Documentation complémentaire

### Identité de marque
- [Brand guidelines](docs/brand/brand-guidelines.md)
- [Usage des logos](docs/brand/logo-usage.md)

### Contenu
- [Profil](docs/content/profile.md)
- [Positionnement](docs/content/positioning.md)
- [Expérience](docs/content/experience.md)
- [Compétences](docs/content/skills.md)
- [Projets](docs/content/projects.md)
- [Sélection des projets](docs/content/project-selection.md)
- [Métriques](docs/content/metrics.md)

### Recherche
- [Inventaire GitHub](docs/research/github-inventory.md)
- [Analyse GitHub](docs/research/github-project-analysis.md)
- [Inventaire technologique](docs/research/technology-inventory.md)
- [Analyse croisée des CV](docs/research/cv-cross-analysis.md)

### UI
- [Design system](docs/ui/design-system.md)
- [Screen map](docs/ui/screen-map.md)
- [Interactions](docs/ui/interactions.md)

## Pre-development package

### New product specifications

- [Interactive 3D developer room](docs/product/three-d-preview.md)
- [Personal Interests](docs/content/personal-interests.md)

### Architecture decisions

- [ADR-001 Modular monolith](docs/architecture/decisions/ADR-001-modular-monolith.md)
- [ADR-002 Frontend rendering](docs/architecture/decisions/ADR-002-frontend-rendering.md)
- [ADR-003 3D preview](docs/architecture/decisions/ADR-003-three-d-preview.md)
- [ADR-004 Multi-agent delivery](docs/architecture/decisions/ADR-004-multi-agent-delivery.md)

### Multi-agent operation

- [Multi-agent workflow](docs/agents/multi-agent-workflow.md)
- [Agent roles](docs/agents/agent-roles.md)
- [Task handoff](docs/agents/task-handoff.md)
- [Context strategy](docs/agents/context-strategy.md)

### Tooling and readiness

- [Plugins, connectors and tooling](docs/integrations/tooling-and-connectors.md)
- [Pre-development checklist](docs/readiness/pre-development-checklist.md)
- [Open decisions](docs/readiness/open-decisions.md)

### Codex configuration

- `.codex/config.toml`
- `.codex/agents/*.toml`
- `.agents/skills/*/SKILL.md`

## Coordination and execution

- [Current project status](PROJECT_STATUS.md)
- [Member responsibilities](docs/coordination/member-responsibilities.md)
- [Communication protocol](docs/coordination/communication-protocol.md)
- [Current repository state](docs/coordination/current-state.md)
- [Decision log](docs/coordination/decision-log.md)
- [Blender to Three.js handoff](docs/coordination/blender-handoff.md)
- [Initial backlog](docs/tracking/backlog.md)
