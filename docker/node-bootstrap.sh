#!/bin/sh
set -eu
cd /var/www/html
echo "[marketplace] Waiting for the Laravel source files..."
until [ -f package.json ]; do sleep 2; done
npm install --no-audit --no-fund
npm run build
echo "[marketplace] Frontend assets built"
exec npm run dev -- --host 0.0.0.0
