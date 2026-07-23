#!/usr/bin/env bash
set -euo pipefail

required=(
  AGENTS.md
  .agents/context-map.tsv
  .agents/model-routing.tsv
  .agents/skills/feature-delivery/SKILL.md
  .codex/config.toml
  .codex/agents/portfolio-terra-worker.toml
  .codex/agents/portfolio-risk-reviewer.toml
  scripts/context-for-issue.sh
  scripts/route-for-issue.sh
  docs/tracking/execution-queue.md
)

for path in "${required[@]}"; do
  test -s "$path" || { echo "Missing or empty required file: $path" >&2; exit 1; }
done

for n in $(seq -w 2 11); do
  id="PORT-0$n"
  test "$(awk -F '\t' -v id="$id" '$1 == id { count++ } END { print count+0 }' .agents/context-map.tsv)" = 1 || { echo "$id must occur once in context map" >&2; exit 1; }
  test "$(awk -F '\t' -v id="$id" '$1 == id { count++ } END { print count+0 }' .agents/model-routing.tsv)" = 1 || { echo "$id must occur once in routing map" >&2; exit 1; }
  rg -q "\| $id " docs/tracking/execution-queue.md || { echo "$id missing from execution queue" >&2; exit 1; }
  while IFS= read -r path; do test -e "$path" || { echo "$id references missing path: $path" >&2; exit 1; }; done < <(bash scripts/context-for-issue.sh "$id")
  bash scripts/route-for-issue.sh "$id" >/dev/null
done

for agent in .codex/agents/*.toml; do
  rg -q '^name = ".+"' "$agent" || { echo "Missing agent name in $agent" >&2; exit 1; }
  rg -q '^description = ".+"' "$agent" || { echo "Missing agent description in $agent" >&2; exit 1; }
  rg -q '^developer_instructions = ' "$agent" || { echo "Missing instructions in $agent" >&2; exit 1; }
done

if rg -n 'max_threads = 6|maximum six|two concurrent write|immediately start the next|Do not wait for a new prompt' AGENTS.md .agents docs/agents docs/tracking/execution-queue.md; then
  echo "Obsolete orchestration instruction detected." >&2
  exit 1
fi

echo "Codex workflow valid: 10 PORT routes, progressive context, one selective subagent and two agent definitions."
