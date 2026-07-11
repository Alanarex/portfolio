# Agent Roles

## Orchestrator

Responsibilities:

- understand requirements;
- decompose work;
- assign ownership;
- prevent conflicts;
- resolve architecture decisions;
- integrate outputs;
- run final tests;
- update tracking.

The orchestrator does not delegate final accountability.

## Explorer

Read-only. Maps code paths, existing patterns, tests and dependencies. Returns exact file and symbol references.

## Architect

Read-only by default. Validates module boundaries, contracts, data ownership, migration impact and ADR needs.

## Backend Worker

Implements Laravel domain/application/HTTP/persistence changes within assigned files.

## Frontend Worker

Implements Blade/Vue/TypeScript/SCSS interfaces within assigned screens and components.

## Three.js Worker

Owns scene integration, asset loading, animations, hotspots, fullscreen behavior, performance and fallback logic.

## DevOps Worker

Owns Docker, CI/CD, environment and deployment configuration within assigned files.

## QA Reviewer

Reviews correctness, security, performance, accessibility and missing tests. Read-only unless explicitly assigned test implementation.

## Documentation Researcher

Verifies version-specific APIs through official documentation and returns concise evidence. Does not edit product code.
