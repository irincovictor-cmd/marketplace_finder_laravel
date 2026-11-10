<?php

namespace Tests\Feature;

use App\Models\DiscoveredListing;
use App\Services\ListingArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveredListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_tracks_first_and_last_seen_without_duplicates(): void
    {
        $item = [
            'id' => 'provider-123',
            'title' => 'iPhone 13 128GB',
            'price' => 12000,
            'city' => 'Tagbilaran',
            'url' => 'https://www.facebook.com/marketplace/item/123',
        ];

        $archive = app(ListingArchiveService::class);
        $this->assertSame(1, $archive->store([$item]));
        $firstSeen = DiscoveredListing::first()->first_seen_at->toIso8601String();

        $this->assertSame(1, $archive->store([[...$item, 'price' => 11000]]));
        $this->assertDatabaseCount('discovered_listings', 1);
        $record = DiscoveredListing::first();
        $this->assertSame($firstSeen, $record->first_seen_at->toIso8601String());
        $this->assertSame('11000.00', $record->price);

        $this->getJson('/listings?q=iphone&max_price=11500')
            ->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.category', 'Phones');
    }

    public function test_listing_filters_and_sorts_only_archived_records(): void
    {
        $archive = app(ListingArchiveService::class);
        $archive->store([
            ['id' => 'phone1', 'title' => 'POCO F7', 'price' => 15000, 'city' => 'Dauis', 'url' => null],
            ['id' => 'laptop1', 'title' => 'ThinkPad laptop', 'price' => 22000, 'city' => 'Panglao', 'url' => null],
        ]);

        $this->getJson('/listings?category=Phones&max_price=20000')
            ->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'POCO F7');

        $this->getJson('/listings?sort=price_high')
            ->assertOk()->assertJsonPath('items.0.title', 'ThinkPad laptop');
        $this->getJson('/listings?sort=invalid')
            ->assertUnprocessable()->assertJsonValidationErrors('sort');
    }

    public function test_items_without_identifiers_are_not_merged_by_title(): void
    {
        $archive = app(ListingArchiveService::class);
        $this->assertSame(0, $archive->store([
            ['title' => 'iPhone 13', 'price' => 15000, 'city' => 'Dauis', 'url' => null, 'id' => ''],
        ]));
        $this->assertDatabaseCount('discovered_listings', 0);
    }
}
