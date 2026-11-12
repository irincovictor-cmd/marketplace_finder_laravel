# MyfirstApp comparison and Marketplace Finder improvements
Date: 2026-11-12

## Reference reviewed
Source: https://github.com/irincovictor-cmd/MyfirstApp

Verified source files: Laravel application tree; StudentController/StudentDetailController; Student/BloodDonor models; BloodDonorController, RequirerController, ContactUsQueryController, StaffController; named routes, role middleware; resources/views/vendor/pagination/custom.blade.php; docs/ERD_AND_MIGRATIONS_TUTORIAL.md.

## What transfers well

| Original pattern | Relevant use in Marketplace Finder | Implementation |
| --- | --- | --- |
| Eloquent fillable and casting | Safe archive and watchlist persistence | Existing models, plus DiscoveredListing activeBohol scope |
| Request validation in controllers | Validate watch terms, budgets, pages | Existing collection controllers; new matching endpoint validates page |
| updateOrCreate for student details | Duplicate-safe watchlist and saved entries | Already used for watchlist entries and saved links |
| Named routes with route model binding | Match a specific watched item by ID | GET /collections/watchlist/{watchlistEntry}/matches |
| Native paginate() in BloodLink | Avoid limit(100/250) truncation | Native paginator and page metadata for deals, watchlist, saved items, watch matches |
| Custom previous/page-number/next view | Clear browsing controls | Added navigation to corresponding SPA sections |
| Staff role middleware and authorization | Future multi-user protection for scan and secret settings | NOT copied: app remains localhost-only, single-user. Add proper auth before public deployment |
| Student hasOne/belongsTo links | Related DB entities | Do not invent FK between generic watch terms and discovered ads: matching is by text and price, not a parent-child association |

### Existing MyfirstApp functionality that should not be ported
Blood donor/requirer records, role-based student/staff dashboards, contact queries, and portfolio pages do not serve deal discovery. Copying them would increase complexity without marketplace value. A separate user/auth module becomes appropriate if Marketplace Finder is hosted beyond localhost.

## Watchlist matching (offline, zero Apify credits)
1. Existing WatchlistEntry has keyword, optional max_price, enabled.
2. WatchlistMatchService selects archived DiscoveredListing rows by Eloquent: Bohol location, reviewable sale, manual availability, all watch-word matches and a price ceiling when supplied.
3. Watchlist shows possible match count, plus 12-per-page matching ads with price, town, last-seen time, and Facebook link.
4. Matches are *candidates*, not verified model equivalence: "iPhone 13" may include variants. Confirm model, condition, and availability with the seller.
5. New approved scans update SQLite; opening Watchlist recalculates matches without further provider requests.
6. Sold, pending and unavailable user-tagged listings are excluded; archived records remain available after reverting the tag.
7. Paused watch entries show no matches until re-enabled; existing Add form can re-enable a normalized keyword.

## Currency inputs
Find deals prices, fresh scan limits, Watchlist budget, and profit calculator prices use text inputs with numeric keyboard hints, because native HTML number controls cannot display grouping commas. JavaScript inserts commas while typing and sends unformatted numeric strings to PHP. Stored prices and Apify payloads remain numeric.

## Native pagination
* Find deals: 24/page, total/page count, stable tie-breaking sort, reset on filter changes.
* Saved bookmarks: 20/page.
* Watchlist: 20/page.
* Matches for each watched device: 12/page.
* Existing rejected and manual history views retain independent pagination.

## Remaining improvement opportunities
* Authentication, authorization policies, and private credential safeguards before hosting online.
* Optional notifications or alert history; current watch matching is on-demand.
* Canonical product model matching to reduce false positives.
* Database indexing/query planning as archived data grows.
* Browser UI and local Docker PHP test verification.
* Legacy geographic and classification parity before expanding paid scans.

## Verification
Feature tests were added for eligible/ineligible watch matches, budgets, manual-sold exclusion, paused entries, pagination past prior fixed limits, and UI controls. Run docker compose exec -T app php artisan test after pulling and rebuilding; no paid scan required.
