<?php

namespace App\Http\Controllers;

use App\Models\DiscoveredListing;
use App\Services\BoholLocationService;
use App\Services\ListingArchiveService;
use App\Services\MarketplaceScanService;
use App\Services\ScanPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class MarketplaceScanController extends Controller
{
    public function preview(Request $request, ScanPlanService $planner): JsonResponse
    {
        $data = $request->validate([
            'keyword' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9 +._-]+$/'],
            'category' => ['nullable', 'in:All,Electronics,Phones,Laptops,Cameras,Consoles,PC Parts,Other'],
            'coverage' => ['nullable', 'in:quick,balanced,broad'],
        ]);

        return response()->json($planner->preview($data['keyword'] ?? '', $data['category'] ?? 'All', $data['coverage'] ?? 'quick'));
    }

    /**
     * Tester scans use the same underlying paid actor and require explicit consent,
     * but cannot launch a 4- or 6-keyword scan from the LAN demo.
     * Route throttle limits total guest scan submissions.
     */
    public function testerScan(
        Request $request,
        MarketplaceScanService $service,
        ListingArchiveService $archive,
        BoholLocationService $location,
        ScanPlanService $planner
    ): JsonResponse {
        if ($request->input('coverage', 'quick') !== 'quick') {
            return response()->json([
                'message' => 'Tester scanning is limited to Quick (up to two billable keyword requests). Use the owner dashboard for broader scans.',
            ], 422);
        }
        return $this->scan($request, $service, $archive, $location, $planner);
    }

    public function scan(
        Request $request,
        MarketplaceScanService $service,
        ListingArchiveService $archive,
        BoholLocationService $location,
        ScanPlanService $planner
    ): JsonResponse {
        $credentialStore = app(\App\Services\ApifyCredentialStore::class);
        if (! app()->environment('local', 'testing') || (! config('marketplace.scan_enabled') && ! $credentialStore->hasStoredToken())) {
            return response()->json(['message' => 'Live scanning is disabled.'], 503);
        }

        $data = $request->validate([
            'keyword' => ['nullable', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9 +._-]+$/'],
            'category' => ['nullable', 'in:All,Electronics,Phones,Laptops,Cameras,Consoles,PC Parts,Other'],
            'coverage' => ['nullable', 'in:quick,balanced,broad'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'integer', 'between:1,100'],
            'sort_by' => ['required', 'in:creation_time_descend,distance_ascend,price_ascend,price_descend'],
            'strict_filtering' => ['sometimes', 'boolean'],
            'confirm_paid_scan' => ['required', 'accepted'],
        ]);

        if (isset($data['min_price'], $data['max_price']) && $data['min_price'] > $data['max_price']) {
            return response()->json(['message' => 'Minimum price cannot exceed maximum price.'], 422);
        }

        if (! $credentialStore->isConfigured()) {
            return response()->json(['message' => 'Add your Apify API key in the dashboard settings first.'], 503);
        }

        $terms = $planner->terms($data['keyword'] ?? '', $data['category'] ?? 'All', $data['coverage'] ?? 'quick');
        $batch = (string) Str::uuid();
        $seen = [];
        $rawCount = 0;
        $errors = [];
        $completed = 0;

        // One to six explicitly approved billable actor requests. Each keyword is an independent run,
        // as in legacy FlipRadar. Dedupe by listing URL or provider id.
        foreach ($terms as $term) {
            try {
                $result = $service->scan([...$data, 'keyword' => $term]);
                $completed++;
                $rawCount += $result['raw_count'];
                foreach ($result['listings'] as $item) {
                    $identity = $item['url'] ?: ($item['id'] ?? '');
                    if (! is_string($identity) || $identity === '') continue;
                    $seen[hash('sha256', $identity)] = $item;
                }
            } catch (RuntimeException $e) {
                $errors[] = $term;
            }
        }

        if ($completed === 0) {
            // Do not include provider errors, headers or account secrets.
            return response()->json([
                'message' => 'All Marketplace provider searches failed. Review Apify status and server configuration.',
                'planned_terms' => $terms,
                'completed_requests' => 0,
                'failed_terms' => $errors,
            ], 502);
        }

        $items = array_values($seen);
        $existing = DiscoveredListing::query()->whereIn('source_key', array_keys($seen))->count();
        $stored = $archive->store($items, $batch);
        $newCount = max(0, $stored - $existing);
        $bohol = $outside = $verify = 0;
        foreach ($items as $item) {
            $status = $location->assess(
                $item['city'] ?? null,
                $item['province'] ?? null,
                $item['location_text'] ?? null,
                $item['latitude'] ?? null,
                $item['longitude'] ?? null
            )['status'];
            if ($status === 'bohol') $bohol++;
            elseif ($status === 'outside') $outside++;
            else $verify++;
        }

        return response()->json([
            'listings' => $items,
            'returned' => count($items),
            'raw_count' => $rawCount,
            'archived_count' => $stored,
            'new_count' => $newCount,
            'previously_seen_count' => $stored - $newCount,
            'scan_batch' => $batch,
            'planned_terms' => $terms,
            'coverage' => $data['coverage'] ?? 'quick',
            'completed_requests' => $completed,
            'failed_terms' => $errors,
            'diagnostics' => [
                'raw_count' => $rawCount,
                'normalized_count' => count($items),
                'archived_count' => $stored,
                'new_count' => $newCount,
                'previously_seen_count' => $stored - $newCount,
                'bohol_count' => $bohol,
                'outside_count' => $outside,
                'unverified_count' => $verify,
            ],
            'keyword' => $data['keyword'] ?? '',
            'category' => $data['category'] ?? null,
            'notice' => 'Keyword probes are not an exhaustive Facebook category search. Charges depend on Apify billing.',
        ]);
    }
}
