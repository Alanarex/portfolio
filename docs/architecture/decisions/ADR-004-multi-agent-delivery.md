# ADR-004: Controlled Multi-Agent Delivery

## Status

Proposed

## Context

The project has parallel domains: backend, dashboard, public UI, Three.js, infrastructure, tests and documentation. Uncontrolled parallel writes can create conflicts and architecture drift.

## Decision

Use a parent/orchestrator agent with specialized subagents. Parallelize read-heavy tasks and independent work. Use separate Git worktrees for simultaneous code-writing agents. The orchestrator owns integration, cross-module decisions and final quality gates.

## Consequences

- faster exploration and review;
- higher token use than single-agent workflows;
- explicit task boundaries and handoff format required;
- no two write agents may own the same files simultaneously.
