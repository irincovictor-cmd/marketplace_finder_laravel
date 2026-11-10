# Marketplace Finder — Laravel

A new, Docker-first Laravel rebuild of [FlipRadar / Marketplace_finder](https://github.com/irincovictor-cmd/Marketplace_finder).

**Status:** Phase 3 initial API milestone. The redesigned Blade dashboard, PHP profit calculator, and **opt-in, single-keyword Laravel Apify scan endpoint** are tracked under `laravel-overlay/`. Legacy FlipRadar remains at `/flipradar/index.html`. Advanced normalization/classification, Bohol location-confidence filters, saved items, watchlist, and historical first-seen tracking **have not yet been migrated**.

## Requirements
- Docker Desktop with Linux containers and WSL integration (Windows), or Docker Engine with Compose (Linux/macOS)
- Internet on first initialization to download the Laravel skeleton and dependencies

No host PHP, Composer or Node.js is required. Docker published ports are restricted to localhost for this development-stage API.

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

The dashboard is a working Blade page with example listings and browser-side sample filtering. The calculator calls POST `/calculator/estimate` on Laravel with CSRF protection. A **separate, disabled-by-default** live scan panel calls POST `/marketplace/scan` only after a checkbox and native browser confirmation. No paid Apify requests occur at page load, build, or test time.

## Phase 3 live scanning (optional, paid)
The Apify Marketplace actor may incur charges and may not provide reliable Bohol-only data. Never assume its `maxTotalChargeUsd=0.10` parameter guarantees a real billing cap.

To enable manually, edit **the local untracked** `laravel/.env` file. Never commit it:

```dotenv
APIFY_TOKEN=your_private_apify_api_token_here
MARKETPLACE_SCAN_ENABLED=true
```

Then restart the app container:

```bash
docker compose restart app
```

Visit http://localhost:8000, scroll to **Live Marketplace scan**, review the warning, and confirm a single request. At most two attempts per minute are allowed on the backend. The actor token is sent in the server-side Authorization header, not in the browser or query string.

**Limitations:** Returned items are basic raw-result mappings, *not* fully cleaned or independently verified results. The requested radius/sort may not be honored by the actor. Do not use these as guaranteed local deals. The original FlipRadar page still uses its old browser-side token handling, so prefer the new Laravel panel for any new token setup. The development endpoints bind to `127.0.0.1` only and do not implement user accounts; do **not** deploy this app publicly before authentication, secure scan authorization, spend limits and server-side rate limiting are reviewed.

To verify without any paid requests:

```bash
docker compose exec -T app php artisan test --filter=MarketplaceScanTest
docker compose exec -T app php artisan test --filter=MarketplaceCalculatorTest
```


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
