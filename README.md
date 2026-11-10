# Marketplace Finder — Laravel

A new, Docker-first Laravel rebuild of [FlipRadar / Marketplace_finder](https://github.com/irincovictor-cmd/Marketplace_finder).

**Status:** Phase 1 compatibility milestone. The original FlipRadar frontend and its JavaScript helpers are checked into `legacy/` and copied into Laravel's public directory on startup. This preserves the UI and browser-side behaviors as a baseline, but **Laravel API, server-side Apify integration, SQLite persistence of browser state, and redesigned Blade pages have not yet been implemented**.

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

Open http://localhost:8000 after the application reports it is ready. The default Laravel welcome page is redirected to the retained FlipRadar frontend at `/flipradar/index.html` on an untouched scaffold. If you previously edited `routes/web.php`, go to http://localhost:8000/flipradar/index.html directly.

The first boot provisions a Laravel 13 skeleton into `laravel/`, creates a local SQLite database, installs Composer dependencies, and runs migrations. On every boot, it also syncs the tracked static compatibility frontend from `legacy/` to `laravel/public/flipradar/`. The node service installs frontend dependencies and starts Vite; visit http://localhost:5173 only for Vite assets (the app is on port 8000).

**Developer note:** `laravel/` is excluded from this initial bootstrap commit because it is generated at first launch. Before feature implementation, the generated Laravel application should be reviewed and committed as the real application source. This is a temporary scaffold, not yet a finished source-complete release.

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
