# Tooling, Plugins and Connectors

## Required now

### GitHub connector

Use for repository analysis, issues, pull requests, reviews and durable implementation context.

### Codex

Use for repository scaffolding, local execution, tests, Docker, quality gates and controlled multi-agent implementation.

### OpenAI Developer Docs MCP

Configured in `.codex/config.toml` for official OpenAI/Codex documentation.

## Add during implementation

### Playwright

Use Playwright CLI and a repository skill for browser flows, screenshots, accessibility smoke tests and fullscreen 3D behavior.

### Sentry

Configure for staging and production error monitoring after the application foundation exists.

### GitLab connector/MCP

Add when GitLab activity synchronization and repository management enter scope.

### Figma connector/MCP

Add only if mockups and design tokens are maintained in Figma. Screenshots alone remain acceptable for initial design references.

## Blender

A ChatGPT plugin is not required for v1. Blender can run locally while Codex generates scripts and the project uses `docs/coordination/blender-handoff.md` as the contract.

A Blender MCP connector is optional later if repeated scene automation becomes valuable. Do not expose arbitrary shell access or unrestricted Python execution without review.

## Avoid initially

- autonomous production deployment tools;
- multiple overlapping documentation MCP servers;
- community connectors with broad filesystem access;
- Linear or another tracker that duplicates the platform's own future tracking system;
- heavy 3D asset generators that do not provide licensing or optimization control.
