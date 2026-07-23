# Catalogue des modules

## Initial modules

| Module | Responsibility |
|---|---|
| Auth | Dashboard authentication and security |
| Profile | Localized professional identity and private CV-version metadata |
| Portfolio | Public content, sections, SEO and publication |
| Media | Images, documents, posters and versioned public assets |
| Projects | Project catalogue and case studies |
| Career | Experience, education and certifications |
| Skills | Skills and technology taxonomy |
| PersonalInterests | Aviation, cars and fishing content |
| Settings | General application settings |
| ActivityLog | Business audit trail |

## Visual module

| Module | Responsibility |
|---|---|
| Portfolio3D | Scene configuration, asset versions, hotspots, fullscreen behavior metadata and public API |

The initial implementation may place Portfolio3D inside Portfolio if a separate module adds overhead without meaningful isolation. The Three.js runtime remains isolated in its own frontend directory either way.

## Intermediate modules

| Module | Responsibility |
|---|---|
| Tasks | Task manager |
| Kanban | Boards, columns and cards |
| Calendar | Deadlines and events |
| Features | Milestones, epics, features and acceptance criteria |
| GitHub | GitHub synchronization and webhooks |
| GitLab | GitLab synchronization and webhooks |
| RepositoryAnalysis | Detected technologies and quality metadata |
| Statistics | KPI and aggregates |
| Monitoring | Application/service health |

## Advanced modules

| Module | Responsibility |
|---|---|
| ModulesRegistry | Reusable package catalogue |
| ProjectFactory | Guided project generation |
| Deployment | Deployment preparation and execution |
| Licensing | Client application activation/licensing |
| Infrastructure | Servers, domains and environments |
| MobileApi | Future mobile API |

## Reusable packages

Reusable source code stays in dedicated Git repositories. The dashboard stores metadata, versions, compatibility, installation documentation, tests and releases; it is not the source-of-truth code store.
