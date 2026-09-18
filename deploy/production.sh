#!/usr/bin/env bash
#
# Production release script.
# Usage: deploy/production.sh <command>
#
#   release   Full zero-downtime-style release: install deps, build assets,
#             run migrations, rebuild caches, restart queue workers.
#   build     Install/build dependencies only (composer + npm), no migrations.
#   migrate   Run pending migrations only.
#   caches    Rebuild config/route/view/event caches.
#   rollback  Restore the previous release's Composer/Node artifacts (see notes below).
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

log() {
    printf '\n==> %s\n' "$1"
}

require_env() {
    if [ ! -f .env ]; then
        echo "Missing .env in production. Copy it from your secrets manager before deploying." >&2
        exit 1
    fi
}

install_dependencies() {
    log "Installing PHP dependencies (no-dev, optimized autoloader)"
    composer install --no-dev --optimize-autoloader --no-interaction

    log "Installing Node dependencies"
    npm ci

    log "Building production frontend assets"
    npm run build
}

run_migrations() {
    log "Running database migrations"
    php artisan migrate --force
}

rebuild_caches() {
    log "Clearing stale caches"
    php artisan optimize:clear

    log "Rebuilding config, route, view and event caches"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    log "Linking public storage"
    php artisan storage:link || true
}

restart_workers() {
    log "Restarting queue workers"
    php artisan queue:restart
}

case "${1:-release}" in
    release)
        require_env
        php artisan down --retry=15 || true
        install_dependencies
        run_migrations
        rebuild_caches
        restart_workers
        php artisan up
        ;;
    build)
        install_dependencies
        ;;
    migrate)
        require_env
        run_migrations
        ;;
    caches)
        rebuild_caches
        ;;
    rollback)
        echo "Rollback must be performed at the deployment/release level (e.g. re-deploy the previous release artifact)." >&2
        echo "This script does not keep release history; ensure your CI/CD or hosting platform retains prior builds." >&2
        exit 1
        ;;
    *)
        echo "Unknown command: ${1}" >&2
        echo "Usage: $0 {release|build|migrate|caches|rollback}" >&2
        exit 1
        ;;
esac

log "Done: ${1:-release}"
