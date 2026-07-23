#!/usr/bin/env bash
set -euo pipefail

id="${1:-}"
if [[ ! "$id" =~ ^PORT-0(0[2-9]|1[01])$ ]]; then
  echo "Usage: $0 PORT-002..PORT-011" >&2
  exit 1
fi

row="$(awk -F '\t' -v id="$id" '$1 == id { print; exit }' .agents/model-routing.tsv)"
test -n "$row" || { echo "No routing policy for $id" >&2; exit 1; }
IFS=$'\t' read -r issue_id class route reason <<<"$row"
printf 'issue=%s\nclass=%s\nroute=%s\nreason=%s\n' "$issue_id" "$class" "$route" "$reason"

case "$route" in
  direct) echo "action=Primary coordinator implements and validates." ;;
  delegate-terra) echo "action=At most one bounded low-risk task may use portfolio_terra_worker; coordinator integrates and validates." ;;
  review-high) echo "action=Primary coordinator implements and tests; portfolio_risk_reviewer performs one read-only review." ;;
  *) echo "Unsupported route: $route" >&2; exit 1 ;;
esac

echo "override=Raise risk for security, privacy, migrations, uploads, concurrency, data integrity, external side effects, shared contracts or unclear scope. Never silently lower it."
