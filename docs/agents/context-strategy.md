# Codex context policy

The normal feature prompt is `next issue`. GitHub supplies live scope; the root `AGENTS.md` supplies stable boundaries.

After selecting one issue, run:

```bash
bash scripts/context-for-issue.sh PORT-NNN
bash scripts/route-for-issue.sh PORT-NNN
```

Read only the returned references, the issue, nearby code/module READMEs and relevant tests. Do not automatically load the full roadmap, backlog, product specification, all ADRs, all content documents or all screenshots.

`.agents/context-map.tsv` is the reviewed on-demand map. UI work must select only the matching audience/route captures in both themes from `docs/design/screenshots/`. Recent implementation facts come from Git history and diffs; progress files are compact caches, not substitutes for GitHub.

Context and routing changes must pass `bash scripts/validate-codex-workflow.sh` so every queued PORT ID stays mapped exactly once and every referenced file exists.
