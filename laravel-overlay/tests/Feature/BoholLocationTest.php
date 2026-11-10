<?php

namespace Tests\Feature;

use App\Services\BoholLocationService;
use App\Services\ListingArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_coordinates_outside_bohol_override_town_label(): void
    {
        $service = app(BoholLocationService::class);
        $this->assertSame('outside', $service->assess('Dauis', 'Bohol', null, 10.31, 123.92)['status']);
    }
}
