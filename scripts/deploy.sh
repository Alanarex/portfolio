#!/usr/bin/env bash
# Runs ON THE SERVER, from the application directory, after the wanted commit is checked out.
# Called by .github/workflows/deploy.yml over SSH, or by hand:  PREVIOUS=<old sha> ./scripts/deploy.sh
#
# 1. builds the images for the checked-out commit
# 2. runs the database migrations
# 3. restarts the stack and refreshes Laravel caches
# 4. waits for /health; on failure, puts the previous release back and exits non-zero
set -euo pipefail

COMPOSE=(docker compose -f compose.prod.yaml)
CURRENT="$(git rev-parse --short=12 HEAD)"
PREVIOUS="${PREVIOUS:-}"
HEALTH_ATTEMPTS="${HEALTH_ATTEMPTS:-20}"

log() { printf '[deploy] %s\n' "$*"; }

if [[ ! -f .env ]]; then
    log "Missing .env in $(pwd). See docs/operations/deployment.md." >&2
    exit 1
fi

wait_for_health() {
    for _ in $(seq 1 "$HEALTH_ATTEMPTS"); do
        if "${COMPOSE[@]}" exec -T web wget -q -O /dev/null http://127.0.0.1/health 2>/dev/null; then
            return 0
        fi
        sleep 3
    done
    return 1
}

start_release() {
    export RELEASE="$1"
    # Keep :latest on the release being started. Without RELEASE in the environment, Compose falls
    # back to :latest, so a manual `docker compose up` would otherwise swap in an older image.
    docker tag "portfolio-app:$1" portfolio-app:latest || log "could not tag portfolio-app:latest" >&2
    docker tag "portfolio-web:$1" portfolio-web:latest || log "could not tag portfolio-web:latest" >&2
    "${COMPOSE[@]}" up -d --remove-orphans
    # Nginx resolves "app" once at startup. When app is recreated but the web image is unchanged
    # (same commit redeployed), web would keep proxying to the old container and /health would 502.
    "${COMPOSE[@]}" restart web
    "${COMPOSE[@]}" exec -T app php artisan optimize
    "${COMPOSE[@]}" exec -T app php artisan queue:restart
}

log "Building release ${CURRENT}"
export RELEASE="${CURRENT}"
"${COMPOSE[@]}" build app web

log "Running migrations"
"${COMPOSE[@]}" up -d postgres redis
"${COMPOSE[@]}" run --rm --no-deps app php artisan migrate --force

log "Starting release ${CURRENT}"
start_release "${CURRENT}"

if wait_for_health; then
    log "Release ${CURRENT} is healthy"
    docker image prune -f --filter "until=168h" >/dev/null || true
    exit 0
fi

log "Release ${CURRENT} failed /health" >&2
if [[ -n "${PREVIOUS}" ]]; then
    PREVIOUS_SHORT="$(git rev-parse --short=12 "${PREVIOUS}")"
    log "Rolling back to ${PREVIOUS_SHORT} (database migrations are NOT rolled back)" >&2
    git checkout --quiet --detach "${PREVIOUS}"
    if ! docker image inspect "portfolio-app:${PREVIOUS_SHORT}" >/dev/null 2>&1; then
        RELEASE="${PREVIOUS_SHORT}" "${COMPOSE[@]}" build app web
    fi
    start_release "${PREVIOUS_SHORT}"
    wait_for_health && log "Previous release ${PREVIOUS_SHORT} restored" >&2
fi
exit 1
