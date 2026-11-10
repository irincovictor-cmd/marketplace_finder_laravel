#!/usr/bin/env bash
set -euo pipefail
cd /var/www/html

# Initial scaffolding is intentionally separate from application migrations.
# Do not overwrite existing application sources.
if [ ! -f artisan ]; then
  echo "[marketplace] First-run Laravel 13 initialization..."
  if [ -n "$(ls -A . 2>/dev/null)" ]; then
    echo "Error: laravel/ contains files but no artisan. Refusing to overwrite." >&2
    exit 1
  fi
  composer create-project laravel/laravel:^13.0 /tmp/marketplace-laravel --no-interaction --prefer-dist
  cp -a /tmp/marketplace-laravel/. /var/www/html/
  rm -rf /tmp/marketplace-laravel
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

# Keep generated secrets and data in the mounted local project, never in Git.
mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
touch database/database.sqlite
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
if ! grep -q '^DB_CONNECTION=' .env; then printf '\nDB_CONNECTION=sqlite\n' >> .env; fi

# Install the verified original static frontend from version-controlled source.
# This is transitional: it is served by Laravel's public directory while
# API and persistence features are migrated into Laravel.
mkdir -p public/flipradar
cp -a /opt/marketplace/legacy/. public/flipradar/

# Phase 2: install version-controlled PHP controller, Blade page, and tests.
# Retain modified user routes, but upgrade our original transitional route.
mkdir -p app/Http/Controllers app/Services app/Models config database/migrations resources/views/marketplace tests/Feature
cp /opt/marketplace/laravel-overlay/app/Http/Controllers/ProfitCalculatorController.php app/Http/Controllers/ProfitCalculatorController.php
cp /opt/marketplace/laravel-overlay/app/Http/Controllers/MarketplaceScanController.php app/Http/Controllers/MarketplaceScanController.php
cp /opt/marketplace/laravel-overlay/app/Http/Controllers/MarketplaceCollectionsController.php app/Http/Controllers/MarketplaceCollectionsController.php
cp /opt/marketplace/laravel-overlay/app/Http/Controllers/DiscoveredListingController.php app/Http/Controllers/DiscoveredListingController.php
cp /opt/marketplace/laravel-overlay/app/Models/DiscoveredListing.php app/Models/DiscoveredListing.php
cp /opt/marketplace/laravel-overlay/app/Services/ListingArchiveService.php app/Services/ListingArchiveService.php
cp /opt/marketplace/laravel-overlay/app/Services/BoholLocationService.php app/Services/BoholLocationService.php
cp /opt/marketplace/laravel-overlay/database/migrations/2026_11_11_000003_add_discovery_location_evidence.php database/migrations/2026_11_11_000003_add_discovery_location_evidence.php
cp /opt/marketplace/laravel-overlay/database/migrations/2026_11_11_000002_create_discovered_listings.php database/migrations/2026_11_11_000002_create_discovered_listings.php
cp /opt/marketplace/laravel-overlay/app/Models/SavedListing.php app/Models/SavedListing.php
cp /opt/marketplace/laravel-overlay/app/Models/WatchlistEntry.php app/Models/WatchlistEntry.php
cp /opt/marketplace/laravel-overlay/database/migrations/2026_11_11_000001_create_marketplace_collections.php database/migrations/2026_11_11_000001_create_marketplace_collections.php
cp /opt/marketplace/laravel-overlay/app/Services/MarketplaceScanService.php app/Services/MarketplaceScanService.php
cp /opt/marketplace/laravel-overlay/config/marketplace.php config/marketplace.php
cp /opt/marketplace/laravel-overlay/resources/views/marketplace/dashboard.blade.php resources/views/marketplace/dashboard.blade.php
cp /opt/marketplace/laravel-overlay/tests/Feature/MarketplaceCalculatorTest.php tests/Feature/MarketplaceCalculatorTest.php
cp /opt/marketplace/laravel-overlay/tests/Feature/MarketplaceScanTest.php tests/Feature/MarketplaceScanTest.php
cp /opt/marketplace/laravel-overlay/tests/Feature/MarketplaceCollectionsTest.php tests/Feature/MarketplaceCollectionsTest.php
cp /opt/marketplace/laravel-overlay/tests/Feature/DiscoveredListingTest.php tests/Feature/DiscoveredListingTest.php
cp /opt/marketplace/laravel-overlay/tests/Feature/BoholLocationTest.php tests/Feature/BoholLocationTest.php
if grep -Fq "return view('welcome');" routes/web.php || grep -Fq "Route::redirect('/', '/flipradar/index.html');" routes/web.php || grep -Fq "Route::view('/', 'marketplace.dashboard')" routes/web.php; then
  cp /opt/marketplace/laravel-overlay/routes/web.php routes/web.php
else
  echo "[marketplace] Existing custom routes/web.php preserved; integrate the new routes manually."
fi

composer install --no-interaction --prefer-dist
if ! grep -Eq '^APP_KEY=base64:.+' .env; then
  php artisan key:generate --force
fi
php artisan migrate --force

echo "[marketplace] Laravel ready at http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
