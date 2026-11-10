# Migration plan — Marketplace Finder

Original: https://github.com/irincovictor-cmd/Marketplace_finder

## Non-negotiable requirements
1. Preserve the original app until each equivalent capability is tested.
2. Cross-machine Docker setup; no host-level PHP/Composer/Node installation.
3. All paid Apify scans require explicit confirmation; protect tokens on the server.
4. Keep SQLite data persistent and provide backup and import/export procedures.
5. Provide automated tests for original logic and Laravel endpoints.
6. Improve mobile-first visual design without changing critical workflow logic.

## Features to carry forward
- Home, search/budget scan, listing details, saved listings, watchlist, calculator
- Dynamic device classification, all-brand phone search, accessories/repair/wanted filtering
- Bohol municipality recognition, radius and province filters, ambiguous-location status
- Adaptive keyword selection and user-visible estimated request costs
- First-seen / NEW / SEEN, viewed items, duplicate/repost grouping and filters
- Asking-price comparisons with clear confidence limits; manual ROI calculator
- Scroll restore, navigation, diagnostics, export, safe Facebook links

## Redesign direction
- Mobile-first responsive dashboard, desktop two-column workspace
- Deep navy/graphite surfaces, warm amber actions, restrained green for verified states
- Search bar, filters, search cost/coverage preview, listing cards and detail pane
- Clear separation between verified facts, approximate results and assumptions
- Clear empty/loading/error states and keyboard-accessible controls

## Migration phases
1. Container/bootstrap foundation (first commit; generated Laravel skeleton still needs to be committed)
2. Laravel source and static frontend parity milestone
3. Secure Apify backend + API validation and rate/cost limits
4. Saved/watchlist/seen migrations from browser storage to SQLite
5. Redesigned views and regression tests against the original
6. Fresh-clone build verification, backup/restore and documentation

**Do not declare functional parity until real-browser and no-paid-scan tests pass.**
