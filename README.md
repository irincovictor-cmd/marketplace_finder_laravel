## Watchlist matches, comma-formatted budgets and native Laravel paging (2026-11-12)

**Watchlist is now connected to the SQLite archive at no extra Apify cost.** Add a device/model keyword and optional buying budget under Watchlist; the screen shows how many archived Bohol-eligible, non-sold sale records match each watched device. Use **View matches** to inspect candidates, last-seen dates, thumbnails when available, and Facebook links. **Pause/Resume** disables or re-enables an entry without deleting it. Matches are possible title/price matches, not precise model guarantees or proof that a seller still has stock. Existing scans update the archive as usual; Watchlist re-evaluates it on demand. A dedicated GET route, `/collections/watchlist/{watchlistEntry}/matches`, uses route model binding and Eloquent pagination (12 results per page), with no paid requests.

Price fields for browsing, approved scanning, watchlist budgets, and profit calculator now insert grouping commas while typing (e.g. `15000` → `15,000`), then strip commas before numeric validation or server calculations. **Find deals** uses 24/page, **Saved items** and **Watchlist** each 20/page, with page totals and Previous/Next buttons rather than truncating the data at old fixed limits. The archived listing model now has an Eloquent active-Bohol scope shared with the watchlist matcher.

The design review of the user's original Laravel project (`MyfirstApp`) and which model/request/pagination conventions were adopted is in [docs/MYFIRSTAPP_REUSE_AND_WATCHLIST.md](docs/MYFIRSTAPP_REUSE_AND_WATCHLIST.md). No unrelated student or blood-donation features were copied, and this remains a localhost-only single-user app: public deployment requires authentication.

## Secure Apify API key setup (no CLI editing)

On `http://localhost:8000`, open **Find deals → Get fresh results → Apify API key — secure local settings**. Paste your `apify_api_...` key, then click **Save / replace key**. The secret is encrypted with this Laravel installation's persistent `APP_KEY` and saved on the server's private `storage/app/private/marketplace/apify-token.enc` disk path. Its plaintext is never returned by the status API, and the app does not put it in browser localStorage, activity logs, or Git. The settings panel supports replacing and removing the saved key; removal does not affect archived listings or manual availability tags.

The key is **not tested against Apify on save** because that could launch an unapproved provider call. Saving a key enables manually approved scans in the localhost development environment; each scan still requires its own plan preview, checkbox, and confirmation dialog. `MARKETPLACE_SCAN_ENABLED` and `APIFY_TOKEN` in local `.env` remain supported for existing installations (environment token is a fallback if no saved key exists). If the encrypted key is deleted, a configured environment key may remain active. Changing `APP_KEY` without migrating the encrypted file makes the saved credential unreadable. Do not commit `.env` or private storage, and never expose this single-user unauthenticated development application to public networks.

The endpoints are `GET/POST/DELETE /marketplace/api-key`, guarded to localhost hosts in local/testing environments and protected by Laravel's CSRF middleware on writes. GET reports status and source only; it never returns the token. `POST` validates the expected Apify key shape. All tests use fake credentials and never contact Apify.

## Manual listing availability (no Apify credits)

Each archived deal card now has a **Your availability tag** selector: Not verified, Available, Pending/reserved, Sold, or Unavailable/removed. These are **user reports**, not Facebook-verified statuses. After viewing the original listing on Facebook, choose a tag; it saves immediately through `PATCH /listings/{id}/manual-status`. Pending, Sold and Unavailable items disappear from default **Find deals** results, but remain in SQLite. **Availability history** in the sidebar (`/#status`) lists all manually tagged records, with original Marketplace links when present and controls to change/undo a tag. Set **Not verified** to clear a tag and return the item to active results; Available records also remain visible.

`GET /listings?availability=active` (default) excludes user-tagged inactive listings, while `availability=all` or a specific status can inspect them for troubleshooting. The separate Rejected listings inspector records `manual_inactive` as a rejection reason. Future Apify rescans update listing details and last-seen times but do **not** reset user-specified availability. No automatic status verification, periodic checking, scraping, or provider charges are triggered by tagging.

This is designed for a **localhost-only single-user** app; add authentication/authorization before making the write endpoint publicly accessible. Manual tags are not evidence of sale or unavailability until the user has checked the source.

## Rejected listings inspector (full archive)

Use **Rejected listings** in the Laravel sidebar (`/#rejected`) to inspect **every stored item excluded from Find deals**. Unlike the original location-only audit (capped at 100 records), the new read-only `GET /listings/rejected` endpoint evaluates every archived record in the selected scope, returns counts by rejection reason, and paginates the full matching set (20, 40, or 100 per page). An item can be rejected for multiple reasons, so counts by reason can exceed the number of rejected items.

Choose **Current Find deals filters** to audit the search term, category, budget, first-discovered window, and last scan batch that the Find deals page currently uses; **Entire archive** to see Bohol/location and sale-intent exclusions across all history; or **Latest scan** (when its batch ID remains in this page session). Reasons include outside Bohol, unverified location, accessory, repair, buying request, keyword mismatch, price below/above range, wrong category, and date mismatch. For each item, the inspector presents the original seller listing link when available, picture when available, city/state, raw location text, coordinate evidence, and the reason given by the current Laravel location and intent rules.

The endpoint uses **no Apify calls, does not mutate records, and does not override unknown locations**. Its decisions are an audit of **current Laravel rules**, not proof of geographic accuracy or complete parity with the legacy classifier. We must still compare the outcomes to legacy `index.html` and `classify.js` using offline fixtures.

## Blank-keyword searches are not exhaustive

A blank keyword in **Apply filters** means no title restriction on **already archived** local data. A blank keyword in **Run approved searches** does **not** scrape all electronics on Facebook: it runs a cost-bounded plan (two terms for the default All/Electronics category, presently `phone` and `laptop`). Legacy FlipRadar's scan planner is configurable and has more keyword terms, but it is not an exhaustive Facebook category crawler either. Broader scans need a separately approved plan because each term can incur Apify charges. The *category* selection on the scan form is local post-scan classification, not a Facebook category feed selector.

The archive also stores `listing_intent`, conservatively excluding buying requests, repair services and clear phone accessories from Bohol sale cards while preserving the raw archive records. **All** includes the archived categories, not just electronics; **Electronics** restricts the archived records to electronic categories. The scan summary distinguishes raw provider rows, normalized unique records, newly archived records and Bohol-evidence matches. Archived Bohol identification relies on city/province evidence, not actual geographic polygon intersection or precise radius filtering. The original municipality table is part of ongoing parity work.

## Legacy functional parity audit (in progress)

The migration is being measured against the original FlipRadar implementation, not redesigned feature by feature. See [the tracked parity audit](docs/LEGACY_PARITY_AUDIT.md) for sources, known gaps and offline verification steps. A pre-audit code backup is available at `backup/pre-legacy-parity-audit-2026-11-11`. The first guarded fix recognizes Xiaomi and other missing legacy device brands, repairs camera categorization and reclassifies existing `Other` records without deleting old listings. The Activity logs sidebar now offers **Inspect excluded listings** to review read-only Bohol filter reasons (up to 100 excluded listings). This does **not** yet certify parity with the legacy radius/geography or wanted/repair/accessory classifier. Avoid live paid scan testing until offline comparison tests pass.

# Marketplace Finder — Laravel

A new, Docker-first Laravel rebuild of [FlipRadar / Marketplace_finder](https://github.com/irincovictor-cmd/Marketplace_finder).

**Status:** Phase 5 UX and discovery history milestone. The Blade interface is simplified into **Find deals / Saved items / Watchlist / Profit calculator**, with advanced paid-scan configuration collapsed by default. Approved scans now archive identifiable results into SQLite with first-seen and last-seen timestamps, and `/listings` supports keyword, category, max-price and sorting filters. Legacy FlipRadar remains at `/flipradar/index.html`. Advanced original classifier/geo logic, browser-data import, automatic watch matching, full UI integration and source-complete portability are still pending.

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

The first boot provisions a Laravel 13 skeleton into `laravel/`, creates a local SQLite database, installs Composer dependencies, and runs migrations. On every boot, it also syncs the tracked static compatibility frontend from `legacy/` to `laravel/public/flipradar/`, plus the new PHP/Blade overlay from `laravel-overlay/`. The node service installs frontend dependencies and starts Vite on Docker's internal network. Port 5173 is intentionally not published to Windows because the current Blade dashboard and legacy UI do not require Vite browser hot reload. The app remains at http://localhost:8000. Browser HMR will be configured explicitly when moving the dashboard styles/scripts to Vite.

**Developer note:** `laravel/` is still excluded because it is generated at first launch; the new PHP and Blade application changes are now tracked explicitly in `laravel-overlay/`. This remains a temporary scaffold requiring a full-source commit and clean-clone verification before release.

## Verify Laravel phase 2
```bash
# From the project root after docker compose up -d --build:
docker compose exec -T app php artisan route:list
docker compose exec -T app php artisan test --filter=MarketplaceCalculatorTest
curl -I http://localhost:8000/
```

The dashboard is a working Blade page with SQLite-backed archived listings and browser-side controls calling server-side filters. The calculator calls POST `/calculator/estimate` on Laravel with CSRF protection. A **separate, disabled-by-default** live scan panel calls POST `/marketplace/scan` only after a checkbox and native browser confirmation. No paid Apify requests occur at page load, build, or test time.

## Bohol-only results (location parity fix)

The app now stores Facebook's reverse-geocoded city/state, location text and coordinates (when returned), and classifies each discovery as `bohol`, `outside` or `verify`. The main `GET /listings` endpoint returns **only `bohol`**. Unknown/ambiguous records and Cebu records stay in SQLite for diagnostics but no longer appear as local recommendations.

This deliberately copies the legacy app's approach of not accepting `PH-07` as evidence of Bohol, rejecting known Cebu municipalities and requiring independent province evidence for ambiguous town names such as Valencia and Carmen. The Bohol municipality reference comes from the original project's `bohol-geography.json`. Listing metadata can be inaccurate: an inferred Bohol match is **not independent verification** of the seller's actual location. The coordinate boundary check is coarse and cannot substitute for official GIS boundaries.

An additive migration reclassifies previously archived records based on their stored town names. Entries without reliable location evidence are hidden, **not deleted**. Previously saved listings and watchlists are unchanged. An approved new scan can provide richer location evidence for further matches.

```bash
docker compose exec -T app php artisan test --filter=BoholLocationTest
docker compose exec -T app php artisan test
```

## Development scan rate limit

The Laravel route-specific `throttle:2,1` limit was **temporarily removed** at the user's request for local development. The browser cooldown/countdown was also removed. Live scans **still require explicit approval**, and each approved submission can run up to two independently billable Apify keyword searches. The UI prevents duplicate clicks while an active request is pending, but rapid successive approved requests are no longer blocked by this application's custom route throttle. Apify or infrastructure-level limits can still apply. Reintroduce a configurable server-side rate limit before any public deployment; this local app is not authenticated.

## Legacy-informed blank keyword scans and latest scan results

The legacy app does not send an unconstrained blank query. It creates a bounded keyword plan based on category. Laravel now mirrors this idea with a conservative maximum of **two** provider requests per approved scan, chosen server-side. Explicit search terms create only one request. Category defaults: Phones (`phone`, `redmi`), Laptops (`laptop`, `notebook`), Cameras (`camera`, `canon`), PC Parts (`rtx`, `ryzen`), Consoles (`ps5`, `switch`), Electronics/All (`phone`, `laptop`). This is intentionally a small exploratory subset, **not** every Facebook Marketplace item in that category. All runs require manual checkbox and a confirmation dialog listing exactly which terms will run; a free `GET /marketplace/scan-plan` endpoint previews the plan. Apify costs are not guaranteed capped.

The app deduplicates items across terms and across history using stable Marketplace URLs/provider IDs; each approved scan gets a unique batch ID. The API responds with `new_count`, `previously_seen_count`, `planned_terms`, `completed_requests`, `failed_terms` and Bohol diagnostics. A failure of every term returns an error; a partial scan keeps successful results while reporting failures. The main UI switches to **Latest scan — Bohol listings** immediately after completion, filtered by its batch ID. **Show all stored listings** returns to the full historical archive. User-set filter fields are synchronized to the scan settings, preventing stale previous filters from hiding the newest batch.

An additive migration adds `last_scan_batch` and preserves pre-existing listings. Historical items have no batch until they occur in a new approved scan. No background scanning or extra image scraping occurs.

## Activity log and empty search diagnostics

The Blade dashboard now includes the legacy-inspired **Activity logs** as its own sidebar section (reachable at `/#activity`) rather than taking space under Find deals. It keeps the most recent 100 events in this browser's localStorage, including local filter/search starts and result counts, failures, explicitly approved scan attempts, aggregate scan counts (raw, normalized, archived, Bohol, outside, unverified), plus manual filter audits. Users can export JSON, copy logs or clear the history. No token, provider raw payload, listing links or authorization header is intentionally recorded. Logs may contain user-entered keywords and prices; review before sharing. Clearing browser storage clears this history.

**Apply filters** searches only archived SQLite listings. Blank search text is fully supported (all models in the chosen category), but does **not** initiate a fresh paid scan. A blank result now explains whether there are no archived listings, no Bohol-eligible listings, or selected filters excluded the Bohol records. Users can open **Get fresh results** separately and explicitly approve any potentially billable Apify request. The local API returns a `diagnostics` object with total stored, Bohol-eligible, matching, and excluded record counts. This does not expose the raw provider response.

To troubleshoot, open the Activity log after clicking Apply filters and check `local_search_completed`; to capture live scanning, explicitly approve the scan and review `scan_completed` or `scan_failed`. Run the PHPUnit suite after rebuilding; automated tests do not call Apify.

## Restore photos from earlier FlipRadar scans

Existing SQLite records from before the image feature may have `image_url = null`. On the **Find deals** screen, use **Restore photos from classic FlipRadar** to copy photo URLs from the original app's `fr_live` browser cache, when both apps have been opened under the same site origin (for example `http://localhost:8000` for both views). The import matches existing Facebook Marketplace item IDs and validates that photos come from an HTTPS Facebook CDN. It doesn't add new listings, call Apify or trigger charges. If the legacy cache is missing, or a matching item never appeared in that cache, restoration will report zero and you'll need a fresh explicitly approved scan to populate the image.

## Marketplace listing photos

Laravel now retains `primary_listing_photo_url` from the approved Apify Marketplace result and stores its HTTPS image URL in the additive `discovered_listings.image_url` SQLite column. Product cards display the **actual Marketplace thumbnail** instead of only category emoji when the actor provides a permitted `fbcdn.net` or `fbsbx.com` URL. The image is loaded lazily in the browser with no referrer; unreadable, missing or expired photos fall back to a category icon. URLs are not fetched on the Laravel server.

This feature does not fetch extra photo details, trigger scans or add API charges by itself. Previously archived listings cannot automatically gain photos if the old scan discarded the image; they'll need to appear in a later explicitly approved scan. Image URLs are external and can expire or become inaccessible.

Tests: `docker compose exec -T app php artisan test --filter=MarketplaceScanTest`, then `docker compose exec -T app php artisan test`.

## Facebook-style browsing filters (November 11, 2026)

The **Find deals** page now supports category-first browsing with an **optional keyword**, Electronics (the umbrella for recognized Phones, Laptops, Cameras, PC Parts and Consoles), minimum/maximum price, sort by most recently found/first discovered/lowest/highest price, and first-discovered date filters.

The browser remembers the last selected settings in localStorage using `marketplace_finder_filters_v1`, and restores them when reopening the page. Blank keyword means **all models** within the selected category; blank minimum/maximum means no bound. Users can choose **Reset to defaults** for All / no price restriction / recently found / Bohol. This is local-browser preference storage, not a multi-user account setting.

Local archived results come from SQLite, and are filtered only after applying the conservative Bohol location gate. The `Electronics` category excludes unclassified `Other` listings; the camera classifier is preliminary and does not guarantee perfect model identification. An additive database migration reclassifies historical records, preserving saved items and existing first/last-seen data.

A fresh Apify scan remains **separate and opt-in**. Its actor supports filter-based searches without a keyword, as well as `minPrice`, `maxPrice` and `sortBy`. However, the actor does **not expose Facebook's exact category-selection algorithm** via those filters. An approved blank-keyword scan may collect mixed categories; the selected category is applied to archived results afterwards, with no guarantee of complete category coverage. Scanning may incur costs and requires explicit approval. No saved filter can launch a scan automatically.

No claim is made to reproduce Facebook's private relevance-ranking algorithm. We reproduce the useful, predictable filter controls for our own archived listings.

Verify:

```bash
docker compose exec -T app php artisan test --filter=DiscoveredListingTest
docker compose exec -T app php artisan test --filter=MarketplaceScanTest
docker compose exec -T app php artisan test
```

## Phase 5: simpler discovery workflow

Open http://localhost:8000 and use the four primary sections:

1. **Find deals** — browse previously discovered, identifiable listings; filter by keyword, price and category; sort by price or recency; save real Facebook listing URLs.
2. **Saved items** — open or remove manually saved listings.
3. **Watchlist** — manage device keywords and target budgets; nothing runs automatically.
4. **Profit calculator** — use the Laravel PHP endpoint to calculate your manual net profit.

The old illustrative cards were removed from the main discovery screen to avoid confusion between sample and genuine provider results. A fresh installation shows a helpful empty state until a user authorizes a scan; the scanner remains disabled by default. A paid scan's normalized results are stored in `discovered_listings` with stable URL/provider-ID deduplication and first/last-seen timestamps. Unknown-identity records are intentionally not archived.

`GET /listings?q=&category=&max_price=&sort=` provides a capped result set (100). Category assignment is preliminary keyword grouping only, not the full legacy classification engine. Provider location accuracy is unverified; the current scan center remains Tagbilaran and must be made configurable in a later milestone. No guarantee of resale profitability is implied.

Test without any paid API request:

```bash
docker compose exec -T app php artisan test --filter=DiscoveredListingTest
docker compose exec -T app php artisan test
```

## Phase 4: saved listings and watchlists in SQLite

The dashboard has separate Watchlist and Saved items views, each with a simple form. Approved scan results with a valid Facebook Marketplace item link also show a **Save listing** button. The API uses `GET/POST /collections/saved`, `DELETE /collections/saved/{id}`, `GET/POST /collections/watchlist` and `DELETE /collections/watchlist/{id}`.

These use `laravel/database/database.sqlite`, which persists across ordinary Docker restarts and `docker compose down`. Data is local and single-user; **there is no authentication or account separation**, so keep port 8000 restricted to localhost. Only HTTPS Facebook Marketplace item links are accepted. Watchlist entries do **not** schedule automatic paid scanning.

The original FlipRadar localStorage is **not synchronized** with SQLite. Do not delete the original browser data or the SQLite file when updating. Test without paid requests:

```bash
docker compose exec -T app php artisan test --filter=MarketplaceCollectionsTest
docker compose exec -T app php artisan test
```

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
