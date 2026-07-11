# ADR-006: Start Portfolio v2 from a Clean Repository State

## Status

Accepted

## Context

The repository contained an unfinished React/TypeScript/Vite prototype and temporary GLB assets. The product owner decided that the final platform should not migrate or reuse that implementation. Historical branches already preserve the previous work.

## Decision

Clean `main` by removing legacy application files, frontend dependencies and temporary 3D assets. Preserve only documentation, governance, GitHub templates, Codex configuration and multi-agent skills before scaffolding Laravel 13 from zero.

Preserve historical code on:

- `release/v1.0.0`;
- `archive/react-v2-prototype-2026-07-11`.

Do not restore files from those branches unless a future GitHub Issue explicitly authorizes it.

## Consequences

- PORT-000 prototype audit is no longer required;
- PORT-002 starts from an unambiguous clean base;
- no accidental React, Tailwind or temporary GLB dependency is inherited;
- previous implementations remain recoverable for historical reference;
- the first Laravel scaffold PR must preserve the documentation and agent setup already on `main`.
