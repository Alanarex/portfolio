#!/usr/bin/env bash
set -euo pipefail

id="${1:-}"
if [[ ! "$id" =~ ^PORT-0(0[2-9]|1[01])$ ]]; then
  echo "Usage: $0 PORT-002..PORT-011" >&2
  exit 1
fi

printf '%s\n' AGENTS.md
awk -F '\t' -v id="$id" '$1 == id { print $2; found=1 } END { exit found ? 0 : 1 }' .agents/context-map.tsv | tr ' ' '\n'

case "$id" in
  PORT-006) find Modules -maxdepth 2 -name README.md -print 2>/dev/null | sort ;;
  PORT-007) find Modules -maxdepth 2 -name README.md -print 2>/dev/null | sort ;;
esac
