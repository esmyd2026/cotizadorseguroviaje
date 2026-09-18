# Deployment & Environment Scripts

Every build/setup/release command for this application is centralized here and in the
`deploy/` scripts below, so local and production environments never drift.

## Local development (Windows / Laragon)

Requires PHP 8.3+, Composer, Node 20+, and a MySQL database already created
(the connection is configured in `.env`).

```powershell
# First-time setup: installs dependencies, prepares .env, migrates, builds assets
.\deploy\local.ps1 setup

# Start php artisan serve + vite together
.\deploy\local.ps1 dev

# Rebuild frontend assets only
.\deploy\local.ps1 build

# Drop and re-run all migrations with seeders
.\deploy\local.ps1 fresh

# Run the Pest test suite
.\deploy\local.ps1 test

# Run Laravel Pint
.\deploy\local.ps1 lint

# setup + lint + test in sequence
.\deploy\local.ps1 all
```

With Laragon, point the site's document root at `public/` and use the configured
virtual host instead of `php artisan serve`; `local.ps1 dev` remains useful for the
Vite dev server.

## Production release

Requires PHP 8.3+, Composer, Node 20+, and a production `.env` already present on
the server (database credentials, `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`).

```bash
# Full release: install deps, build assets, migrate, rebuild caches, restart queues
./deploy/production.sh release

# Install/build dependencies only, no migrations
./deploy/production.sh build

# Run pending migrations only
./deploy/production.sh migrate

# Rebuild config/route/view/event caches only
./deploy/production.sh caches
```

`release` wraps the application in maintenance mode (`php artisan down`) for the
duration of the deploy and brings it back up (`php artisan up`) once caches are
rebuilt and queue workers are restarted.

## Notes

- Both scripts assume `composer`, `npm`, and `php` are already on the `PATH`.
- Neither script touches Git; source control operations are handled separately.
- The production script never runs `migrate:fresh` or any destructive command —
  only forward migrations (`migrate --force`).
