# ADR-001: Start as a Modular Laravel Monolith

## Status

Accepted

## Context

The platform contains public CMS content, project management, integrations, demos, deployment, licensing and observability. Starting with independent services would add deployment and operational complexity before module boundaries are proven.

## Decision

Use one Laravel 13 application with explicit modules and contracts. Extract a service only when operational or domain evidence justifies it.

The initial platform will use PostgreSQL, Redis, queues and the Laravel scheduler inside one deployable application. Module ownership and contracts remain strict even though deployment is unified.

## Consequences

- faster initial delivery;
- one deployment unit;
- simpler transactions;
- strict module boundaries remain mandatory;
- cross-module coupling must be monitored;
- service extraction remains possible after real operational evidence exists.
