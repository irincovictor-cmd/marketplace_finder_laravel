#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

echo "Checking Docker services..."
docker compose ps
echo "Checking Laravel runtime..."
docker compose exec -T app php artisan --version

echo "Checking original FlipRadar assets in Laravel's public directory..."
for path in index.html classify.js discovery.js plan.js nav.js seen.js bohol-geography.json; do
  docker compose exec -T app test -s "/var/www/html/public/flipradar/$path"
  echo "  OK: $path"
done

echo "Checking HTTP endpoint..."
curl -fsS http://localhost:8000/flipradar/index.html -o /dev/null
echo "  OK: http://localhost:8000/flipradar/index.html"

echo "Smoke test passed. Live Apify scans and browser interactions are NOT covered by this test."
