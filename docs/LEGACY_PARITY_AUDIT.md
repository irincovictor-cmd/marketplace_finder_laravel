# Marketplace Finder: Legacy FlipRadar parity audit
Date: 2026-11-11
Status: **Phase 1 — baseline established; parity NOT yet certified**

## Sources of truth
- Original: `irincovictor-cmd/Marketplace_finder` — `index.html`, `plan.js`, `discovery.js`, `classify.js`, `bohol-geography.json`.
- Laravel: `laravel-overlay/app/Services/`, controllers, migrations, Blade dashboard, and feature tests.
- Protected baseline branch: `backup/pre-legacy-parity-audit-2026-11-11`.
- Never call the live Apify actor from automated tests. Keep credentials in local .env only.

## Findings and fixes in Phase 1

| Concern | Legacy behavior observed | Laravel before audit | Current action |
|---|---|---|---|
| Phone classification | `classify.js` recognizes Xiaomi, Infinix, Tecno and other brands and distinguishes accessory/repair/wanted/review | Short brand list omitted Xiaomi | **Fixed recognition of missing device brands** in `ListingArchiveService`; regression fixtures added. Full classification parity pending. |
| Camera classification | DSLR, mirrorless, Canon, etc. detected distinctly | Camera regex had a double-escaped word boundary | **Corrected regex**; added Canon/Sony camera tests. |
| Existing archive records | Legacy localStorage data persisted across scans | Previously archived `Other` entries stayed miscategorized after classifying logic changed | **Additive migration 000007** reclassifies only previously `Other` rows, preserving existing identities and timestamps. |
| Bohol location | 48-municipality dataset, town aliases, province interpretation, coordinates and distance from chosen center; unknown is not automatically local | Static list and conservative geographic rectangle; no radius-based ranking/verification | **No blind relaxation.** Read-only excluded-listing inspector now exposes reason and available metadata for audit. Exact legacy geographic parity pending. |
| Rejection inspection | Original Search view exposes rejected item and reasons such as keyword, category, budget, province and unknown distance | Only a summary excluded count | **Added first read-only location-inspector slice** in Activity logs, with counts and up to 100 rows, without exposing hidden listings as Bohol verified. Budget/keyword/category diagnostics pending. |
| Keyword planning | `plan.js` uses broad+brand term pools with usage history and maximum paid search count | Bounded hardcoded two-term plan | **Not equivalent yet**. Do not automatically expand paid plan; requires user approval and billing safeguards. |
| Listing capture | Legacy cleans keyword results and analyzes listing types | Laravel mostly filters sold/pending and positive price | Full parity pending; needs offline JSON fixtures from representative provider responses. |
| Images | Legacy preserves primary listing photo | Laravel uses that field but older rows may have null image and browser cache may be on another origin | Image backfill parity pending; do not invent item photos. |
| Fresh results | Legacy tracks seen/new across scans and archives results | Laravel scan batches + stored archive | Partially implemented; offline identity and deduplication parity still to be verified. |

## Verification plan
1. Run the offline feature tests: `docker compose exec -T app php artisan test`.
2. Inspect the exclusion table from **Activity logs → Inspect excluded listings**. This is read-only.
3. Compare concrete old/new decisions with deterministic fixtures: Bohol unique municipality, ambiguous town + PH-07, explicit Cebu state, explicit Bohol state, missing city, and coordinates outside the supported area.
4. Expand fixtures to cover wanted/repair/accessory/unknown device classification. Do not silently label unknowns as phones.
5. Port legacy province/town and radius policy with explicit tests before running any migration that changes location status.
6. Only after offline equivalence, perform one specifically approved real scan and inspect raw → normalized → unique → Bohol → visible metrics. Apify billing is separate.

## Current known limits
- The location inspector reports the **current** Laravel location decision, not an independently computed legacy decision. The word `verify` means insufficient evidence, not confirmed outside.
- Historical records may lack original Apify metadata; we cannot infer it retroactively. Repeated live scans cost money and are not an acceptable substitute for tests.
- The Laravel UI's category labels remain coarse, and `classify.js` is richer than the PHP classifier.
- Browser JavaScript syntax and GitHub commits alone do not demonstrate passing PHPUnit or visual/browser correctness. Tests still need running in the user's Docker environment.
