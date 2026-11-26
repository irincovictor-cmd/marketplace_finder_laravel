# Marketplace Finder

**A web-based tool for finding, organizing, and evaluating Facebook Marketplace listings in Bohol, Philippines.**

Academic software project · Functional prototype

## About the system

Marketplace Finder helps buyers and resellers find second-hand products without repeatedly searching Facebook Marketplace manually. It collects listings through the Apify service, organizes them in a local database, and provides tools for comparing prices, monitoring products, and estimating potential resale profit.

The system focuses on Bohol so users can prioritize nearby deals. It helps with decision-making; actual product condition, availability, location and seller credibility must still be checked on Facebook.

## Main objectives

- Make searching for second-hand products easier and more organized.
- Reduce irrelevant results using category, price and location filters.
- Keep a searchable history of discovered products.
- Help users assess potential resale profit before purchasing.

## Main features

| Feature | Purpose |
| --- | --- |
| Find deals | Browse collected listings or search for new Facebook Marketplace products. |
| Search filters | Narrow results by keyword, category, price and date; sort by price or recency. |
| Listing photos | Display original seller photos when available, cache retrieved photos, and request recovery of missing photos. |
| Saved items | Keep interesting products for later comparison. |
| Watchlist | Set a product keyword and target budget, then view matching archived listings. |
| Profit calculator | Estimate potential profit using purchase price, resale price and expenses. |
| Rejected listings | See why products were excluded, including uncertain locations or unsuitable advertisements. |
| Availability history | Manually mark a listing as available, reserved, sold or unavailable. |
| Activity logs | Review search activity and troubleshooting information. |
| Settings | Manage and test the Apify API key and recover listing photos. |

## How the system works

1. **Search:** The user selects a product keyword or category and preferred price range.
2. **Collect:** After the user approves a live search, Apify retrieves Marketplace listings.
3. **Filter:** Laravel checks listing identity, location evidence and product information. Uncertain or unsuitable records are kept separately for review.
4. **Store:** SQLite preserves discovered listings and their discovery history.
5. **Review:** The dashboard displays eligible results, seller photographs when available, prices and links to the original advertisements.
6. **Evaluate:** The user can save products, compare watchlist matches or estimate resale profit.

Browsing previously collected listings does not require a paid Apify search. New searches and provider-based photo recovery may consume credits and require explicit approval.

## Suggested demonstration

1. Open **Find deals** and browse the existing archive.
2. Apply a category and price filter, then open a seller's original listing.
3. Save an interesting product and view it in **Saved items**.
4. Create a **Watchlist** entry and inspect its matching products.
5. Use the **Profit calculator** to estimate the return on a possible purchase.
6. Open **Rejected listings** or **Availability history** to demonstrate how the system organizes unsuitable or inactive advertisements.
7. Show **Settings** and the API connection checker. A live search or photo recovery is optional and requires approval.

## Technology used

| Technology | Role |
| --- | --- |
| Laravel / PHP | Application logic, validation and API endpoints |
| Blade, HTML, CSS and JavaScript | Responsive browser interface |
| SQLite | Persistent listings, saved items and watchlist data |
| Apify | External Marketplace data collection |
| Docker Compose and Nginx | Local services and supervised demo access |
| Cloudflare Tunnel | Temporary remote demo access when launched separately |

## Run the project

**Requirements:** Git, Docker with Compose, and an internet connection for initial setup. On Windows, enable Docker Desktop integration with WSL2 Ubuntu.

```bash
git clone https://github.com/irincovictor-cmd/marketplace_finder_laravel.git
cd marketplace_finder_laravel
docker compose up -d --build
docker compose ps
```

Once the application is ready, open [http://localhost:8000/discover](http://localhost:8000/discover).

- **Local owner:** Full dashboard and settings.
- **Demo interface:** [http://localhost:8080/browse](http://localhost:8080/browse).
- **Remote owner:** Use the supervised demo link supplied by the host and sign in through its `/demo-access` page. The private code is available on the local owner's `/demo-access` page. Never publish this code or the API key.

A fresh installation starts with an empty archive. To collect new listings, configure a valid Apify key in Settings and approve a search. Existing data on the host is not included in the public repository.

## Scope and limitations

This is an academic prototype, not a production multi-user marketplace. Search coverage and location accuracy depend on the external provider and available listing metadata. Blank-keyword searches use limited keyword probes rather than a complete Facebook category feed. Original photos may be unavailable if their source URLs expire or the seller removes the listing. Availability is manually reported, and estimated profit is not guaranteed.

The demo workspace shares the host's data and provider account. Share access only during a supervised demonstration. No API key, private access code or personal database belongs in GitHub.

## Developer references

- [Migration notes](docs/MIGRATION.md)
- [Legacy parity audit](docs/LEGACY_PARITY_AUDIT.md)
- [Watchlist implementation](docs/MYFIRSTAPP_REUSE_AND_WATCHLIST.md)

Run automated tests with `docker compose exec -T app php artisan test`. To stop the local services, use `docker compose down`. Keep the local database, application encryption key and private storage when updating.

---

*Marketplace Finder — helping users discover, organize and evaluate second-hand deals in Bohol.*
