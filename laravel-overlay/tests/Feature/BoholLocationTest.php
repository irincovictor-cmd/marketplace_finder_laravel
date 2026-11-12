<?php

namespace Tests\Feature;

use App\Services\BoholLocationService;
use App\Services\ListingArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BoholLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bohol_towns_pass_but_cebu_and_unknown_places_do_not(): void
    {
        $service = app(BoholLocationService::class);
        $this->assertSame('bohol', $service->assess('Tagbilaran City')['status']);
        $this->assertSame('bohol', $service->assess('Dauis', 'PH-07')['status']);
        $this->assertSame('outside', $service->assess('Mandaue', 'Cebu')['status']);
        $this->assertSame('outside', $service->assess('Cebu City')['status']);
        $this->assertSame('verify', $service->assess('', 'PH-07')['status']);
        $this->assertSame('verify', $service->assess('Valencia', 'PH-07')['status']);
        $this->assertSame('bohol', $service->assess('Valencia', 'Bohol')['status']);
        $this->assertSame('outside', $service->assess('Panglao', 'Cebu')['status']);
    }

    public function test_mixed_scan_results_only_show_local_bohoI_results(): void
    {
        $archive = app(ListingArchiveService::class);
        $archive->store([
            ['id' => 'b1', 'title' => 'RTX 5060 Bohol', 'price' => 20000, 'city' => 'Tagbilaran', 'province' => 'Bohol', 'url' => null],
            ['id' => 'c1', 'title' => 'RTX 5060 Cebu', 'price' => 20000, 'city' => 'Cebu City', 'province' => 'Cebu', 'url' => null],
            ['id' => 'u1', 'title' => 'RTX 5060 Unknown', 'price' => 20000, 'city' => '', 'province' => 'PH-07', 'url' => null],
            ['id' => 'a1', 'title' => 'RTX 5060 Ambiguous', 'price' => 20000, 'city' => 'Valencia', 'province' => 'PH-07', 'url' => null],
        ]);
        $this->assertDatabaseCount('discovered_listings', 4);
        $this->getJson('/listings?q=RTX%205060')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'RTX 5060 Bohol')
            ->assertJsonPath('items.0.location_status', 'bohol');
    }

    public function test_nested_provider_reverse_geocode_is_filtered_after_scan(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/*' => Http::response([
            ['id' => 'fb-1', 'marketplace_listing_title' => 'RTX 5060 Cebu',
             'listing_price' => ['amount' => 27000],
             'location' => ['reverse_geocode' => ['city' => 'Mandaue', 'state' => 'Cebu']]],
            ['id' => 'fb-2', 'marketplace_listing_title' => 'RTX 5060 Bohol',
             'listing_price' => ['amount' => 23000],
             'location' => ['reverse_geocode' => ['city' => 'Tagbilaran City', 'state' => 'Bohol']]],
        ], 200)]);
        $this->postJson('/marketplace/scan', [
            'keyword' => 'rtx 5060', 'latitude' => 9.6477, 'longitude' => 123.8547,
            'radius_km' => 40, 'sort_by' => 'creation_time_descend',
            'confirm_paid_scan' => true,
        ])->assertOk()->assertJsonPath('archived_count', 2);
        $this->getJson('/listings')->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'RTX 5060 Bohol')
            ->assertJsonPath('excluded_unverified', 1);
    }

    public function test_coordinates_outside_bohol_override_town_label(): void
    {
        $service = app(BoholLocationService::class);
        $this->assertSame('outside', $service->assess('Dauis', 'Bohol', null, 10.31, 123.92)['status']);
    }
    public function test_rejected_listing_inspector_reports_evidence_without_changing_visibility(): void
    {
        $archive = app(ListingArchiveService::class);
        $archive->store([
            ['id' => 'audit-bohol', 'title' => 'Xiaomi 14 Bohol', 'price' => 9000,
             'city' => 'Tagbilaran', 'province' => 'Bohol', 'url' => null],
            ['id' => 'audit-cebu', 'title' => 'Xiaomi 14 Cebu', 'price' => 8500,
             'city' => 'Mandaue', 'province' => 'Cebu', 'url' => null],
            ['id' => 'audit-unknown', 'title' => 'Xiaomi 14 Unknown', 'price' => 8200,
             'city' => '', 'province' => 'PH-07', 'url' => null],
        ]);

        $response = $this->getJson('/listings/location-audit')->assertOk()
            ->assertJsonPath('counts.bohol', 1)
            ->assertJsonPath('counts.outside', 1)
            ->assertJsonPath('counts.verify', 1)
            ->assertJsonPath('total_rejected', 2)
            ->assertJsonPath('read_only', true)
            ->assertJsonCount(2, 'sample');
        $this->assertContains('Other province stated', array_column($response->json('sample'), 'reason'));
        $this->getJson('/listings?q=Xiaomi')->assertOk()->assertJsonCount(1, 'items');
        $this->assertDatabaseCount('discovered_listings', 3);
    }

    public function test_ambiguous_town_requires_province_even_when_region_code_is_present(): void
    {
        $service = app(BoholLocationService::class);
        $this->assertSame('verify', $service->assess('Carmen', 'PH-07')['status']);
        $this->assertSame('bohol', $service->assess('Carmen', 'Bohol')['status']);
        $this->assertSame('verify', $service->assess('Valencia', 'PH-07')['status']);
        $this->assertSame('outside', $service->assess('Mandaue', 'Bohol')['status']);
    }

    public function test_ambiguous_carmen_can_be_verified_after_checking_source_and_undone(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'carmen-phone', 'title' => 'Redmi 15 phone', 'price' => 11999,
             'city' => 'Carmen', 'province' => 'PH-07', 'url' => null],
            ['id' => 'carmen-cebu', 'title' => 'Laptop in Carmen Cebu', 'price' => 12500,
             'city' => 'Carmen', 'province' => 'Cebu', 'url' => null],
        ]);

        $ambiguous = \App\Models\DiscoveredListing::query()->where('title', 'Redmi 15 phone')->firstOrFail();
        $cebu = \App\Models\DiscoveredListing::query()->where('title', 'Laptop in Carmen Cebu')->firstOrFail();
        $this->assertSame('verify', $ambiguous->location_status);

        $this->getJson('/listings')->assertOk()->assertJsonCount(0, 'items');
        $this->patchJson('/listings/'.$cebu->id.'/location-review', ['decision' => 'bohol'])
            ->assertUnprocessable();
        $this->patchJson('/listings/'.$ambiguous->id.'/location-review', ['decision' => 'bohol'])
            ->assertOk()->assertJsonPath('manual_location', 'bohol')
            ->assertJsonPath('automatic_location_status', 'verify');

        $this->getJson('/listings')->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.manual_location', 'bohol')
            ->assertJsonPath('items.0.automatic_location_status', 'verify');
        $this->getJson('/listings/rejected')->assertOk()
            ->assertJsonPath('rejected_count', 1);
        // A later scan must not silently undo the user's check.
        app(ListingArchiveService::class)->store([
            ['id' => 'carmen-phone', 'title' => 'Redmi 15 phone', 'price' => 11500,
             'city' => 'Carmen', 'province' => 'PH-07', 'url' => null],
        ]);
        $this->assertDatabaseHas('discovered_listings', [
            'id' => $ambiguous->id, 'manual_location' => 'bohol',
            'location_status' => 'verify', 'price' => 11500,
        ]);

        $this->patchJson('/listings/'.$ambiguous->id.'/location-review', ['decision' => 'auto'])
            ->assertOk()->assertJsonPath('manual_location', null)
            ->assertJsonPath('location_status', 'verify');
        $this->getJson('/listings')->assertOk()->assertJsonCount(0, 'items');
        $this->assertDatabaseCount('discovered_listings', 2);
    }

    public function test_manual_location_review_rejects_invalid_decisions(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'town-1', 'title' => 'Phone for sale', 'price' => 8500,
             'city' => 'Carmen', 'province' => 'PH-07', 'url' => null],
        ]);
        $id = \App\Models\DiscoveredListing::query()->firstOrFail()->id;
        $this->patchJson('/listings/'.$id.'/location-review', ['decision' => 'somewhere'])
            ->assertUnprocessable()->assertJsonValidationErrors('decision');
        $this->patchJson('/listings/999999/location-review', ['decision' => 'bohol'])->assertNotFound();
    }


}
