# ADR-005: Portfolio v2 Scaffold Baseline

## Status

Accepted

## Context

Laravel scaffolding requires stable decisions for persistence, package management, testing, authentication and deployment. Leaving these choices implicit would create inconsistent agent output and unnecessary rework.

## Decision

Use the following baseline for portfolio v2:

- **Framework:** Laravel 13 on PHP 8.3+;
- **Database:** PostgreSQL as the primary relational database;
- **Cache and queues:** Redis;
- **JavaScript package manager:** npm;
- **PHP test framework:** PHPUnit;
- **Public rendering:** Laravel Blade/server-rendered HTML;
- **Dashboard frontend:** Vue 3 + TypeScript with Inertia;
- **3D runtime:** vanilla Three.js + TypeScript as an isolated lazy-loaded module;
- **Authentication:** one private administrator in the initial release, with authorization boundaries designed for later multi-user RBAC;
- **Local environment:** Docker Compose with Nginx, PostgreSQL, Redis, queue worker, scheduler and Mailpit;
- **Production target:** OVH VPS with Docker, Nginx, PostgreSQL, Redis, workers, scheduler, monitoring and backups;
- **Asset strategy:** private Blender sources separated from optimized public GLB/poster assets;
- **Legacy prototype:** audit first, migrate useful parts, then archive or remove only with documented evidence.

## Consequences

- Codex can scaffold against one unambiguous stack;
- PostgreSQL-specific behavior must be covered by tests and local containers;
- the first authentication implementation remains intentionally narrow;
- RBAC-ready authorization boundaries must not be confused with immediate multi-user administration;
- deployment automation must target OVH VPS/Docker without embedding production credentials;
- npm lockfiles become part of reproducible builds;
- PHPUnit remains the canonical PHP test runner.
