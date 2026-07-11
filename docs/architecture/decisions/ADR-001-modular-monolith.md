# ADR-001: Start as a Modular Laravel Monolith

## Status

Proposed

## Context

The platform contains public CMS content, project management, integrations, demos, deployment, licensing and observability. Starting with independent services would add deployment and operational complexity before module boundaries are proven.

## Decision

Use one Laravel application with explicit modules and contracts. Extract a service only when operational or domain evidence justifies it.

## Consequences

- faster initial delivery;
- one deployment unit;
- simpler transactions;
- strict module boundaries remain mandatory;
- cross-module coupling must be monitored.
