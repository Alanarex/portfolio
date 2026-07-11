# Multi-Agent Workflow

## Operating model

One orchestrator owns the task. Specialized agents receive bounded work and return structured evidence. The orchestrator integrates results and runs final checks.

## Core rule

Parallelize read-heavy and independent work. Serialize shared write-heavy work.

Good parallel work:

- repository exploration;
- architecture review;
- dependency research;
- security review;
- test-gap analysis;
- documentation audit;
- independent modules in separate worktrees.

Bad parallel work:

- multiple agents editing the same controller, module provider, route file or frontend entry point;
- simultaneous database schema changes without one owner;
- several agents changing shared design tokens;
- parallel merges into the same branch.

## Standard workflow

1. Orchestrator reads the feature file and relevant documentation.
2. Orchestrator creates a task decomposition.
3. Explorer/architect agents gather evidence in parallel.
4. Orchestrator resolves boundaries and assigns file ownership.
5. Write agents work in isolated worktrees when concurrent.
6. QA/reviewer agents inspect results.
7. Orchestrator integrates branches.
8. Orchestrator runs full relevant quality gates.
9. Orchestrator updates feature status and documentation.

## Concurrency policy

- default maximum active threads: 6;
- maximum agent nesting depth: 1;
- no recursive delegation;
- maximum two simultaneous write agents initially;
- one owner per file path;
- one owner for database schema per feature;
- one owner for shared frontend entry points.

## Token-control policy

- use the explorer agent for fast read-heavy scans;
- return summaries, not raw logs;
- do not spawn agents for trivial tasks;
- use one agent per genuinely independent concern;
- stop after sufficient evidence;
- avoid duplicated repository scans;
- use skills for repeated workflows;
- keep project context in files, not repeated prompts.

## Recommended prompt

```text
Implement FEATURE-ID using controlled subagents.

First, have the explorer map affected modules and tests, the architect validate boundaries, and the reviewer identify security/quality risks. Wait for their summaries.

Then propose file ownership. Use separate worktrees only for independent write tasks. Do not let two agents edit the same files. Integrate centrally and run the complete relevant quality gates before marking the feature done.
```
