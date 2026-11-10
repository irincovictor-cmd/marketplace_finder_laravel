# Marketplace Finder — Laravel

A new, Docker-first Laravel rebuild of [FlipRadar / Marketplace_finder](https://github.com/irincovictor-cmd/Marketplace_finder).

**Status:** Phase 2 Laravel-native milestone. A redesigned Blade dashboard and a functional PHP calculator endpoint are now stored in `laravel-overlay/`, and installed into the Laravel container on startup. The original FlipRadar remains accessible at `/flipradar/index.html` for functional comparison. The scanner, watchlist, saved data and geographic filters are **not yet ported to Laravel**.

## Requirements
- Docker Desktop with Linux containers and WSL integration (Windows), or Docker Engine with Compose (Linux/macOS)
- Internet on first initialization to download the Laravel skeleton and dependencies

No host PHP, Composer or Node.js is required.

## Setup
```bash
git clone https://github.com/irincovictor-cmd/marketplace_finder_laravel.git
cd marketplace_finder_laravel
docker compose up -d --build
docker compose logs -f app
```

Open http://localhost:8000 for the new Blade dashboard and PHP-backed calculator. The original FlipRadar still works at http://localhost:8000/flipradar/index.html. If you modified `routes/web.php` yourself, the bootstrap script preserves it instead of replacing it.

The first boot provisions a Laravel 13 skeleton into `laravel/`, creates a local SQLite database, installs Composer dependencies, and runs migrations. On every boot, it also syncs the tracked static compatibility frontend from `legacy/` to `laravel/public/flipradar/`, plus the new PHP/Blade overlay from `laravel-overlay/`. The node service installs frontend dependencies and starts Vite; visit http://localhost:5173 only for Vite assets (the app is on port 8000).

**Developer note:** `laravel/` is still excluded because it is generated at first launch; the new PHP and Blade application changes are now tracked explicitly in `laravel-overlay/`. This remains a temporary scaffold requiring a full-source commit and clean-clone verification before release.

## Verify Laravel phase 2
```bash
# From the project root after docker compose up -d --build:
docker compose exec -T app php artisan route:list
docker compose exec -T app php artisan test --filter=MarketplaceCalculatorTest
curl -I http://localhost:8000/
```

The dashboard is a working Blade page with example listings and browser-side sample filtering. The calculator calls POST `/calculator/estimate` on Laravel with CSRF protection. No paid Apify requests occur from the new dashboard.

## Operational commands
```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose down
```

`docker compose down` does not remove project files. Do not use `down -v` unless you intend to remove Docker volumes.

## Security and data
- Do not commit `laravel/.env`, API keys, or `laravel/database/database.sqlite`.
- Live Apify scans must require explicit user approval and use server-side secrets; **no paid scan is part of bootstrap**.
- Existing FlipRadar browser localStorage is not imported automatically. Back up existing saved items before migration. The legacy page still uses browser localStorage and the original browser-side Apify requests; live scans are unverified and may incur Apify charges.
- The original static repository remains unchanged.

See `docs/MIGRATION.md` for the functionality-preservation checklist and redesign direction.
