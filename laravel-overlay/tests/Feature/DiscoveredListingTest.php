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

    public function test_blank_search_returns_all_eligible_local_bohol_listings_and_diagnostics(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'b1', 'title' => 'iPhone 13', 'price' => 9000, 'city' => 'Tagbilaran', 'url' => null],
            ['id' => 'b2', 'title' => 'Canon camera', 'price' => 16000, 'city' => 'Dauis', 'url' => null],
            ['id' => 'c3', 'title' => 'iPhone Cebu', 'price' => 5000, 'city' => 'Mandaue', 'province' => 'Cebu', 'url' => null],
        ]);
        $this->getJson('/listings?q=')
            ->assertOk()->assertJsonCount(2, 'items')
            ->assertJsonPath('diagnostics.stored_total', 3)
            ->assertJsonPath('diagnostics.bohol_total', 2)
            ->assertJsonPath('diagnostics.hidden_outside_or_unknown', 1)
            ->assertJsonPath('diagnostics.keyword_supplied', false);

        $this->getJson('/listings?category=Electronics&q=')
            ->assertOk()->assertJsonCount(2, 'items');
        $this->getJson('/listings?q=&min_price=17000')
            ->assertOk()->assertJsonCount(0, 'items')
            ->assertJsonPath('diagnostics.matching_filters', 0);
    }

    public function test_items_without_identifiers_are_not_merged_by_title(): void
    {
        $archive = app(ListingArchiveService::class);
        $this->assertSame(0, $archive->store([
            ['title' => 'iPhone 13', 'price' => 15000, 'city' => 'Dauis', 'url' => null, 'id' => ''],
        ]));
        $this->assertDatabaseCount('discovered_listings', 0);
    }
    public function test_optional_keyword_electronics_category_price_range_and_sort(): void
    {
        $archive = app(ListingArchiveService::class);
        $archive->store([
            ['id' => 'camera-1', 'title' => 'Canon EOS camera', 'price' => 9500, 'city' => 'Dauis', 'url' => null],
            ['id' => 'phone-2', 'title' => 'iPhone 13 128GB', 'price' => 14000, 'city' => 'Panglao', 'url' => null],
            ['id' => 'laptop-3', 'title' => 'ThinkPad laptop', 'price' => 18000, 'city' => 'Tagbilaran', 'url' => null],
            ['id' => 'other-4', 'title' => 'Wooden table', 'price' => 12000, 'city' => 'Tagbilaran', 'url' => null],
        ]);

        // Empty search term browses every recognized electronic model.
        $this->getJson('/listings?category=Electronics&min_price=9000&max_price=15000&sort=price_low')
            ->assertOk()->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.category', 'Cameras')
            ->assertJsonPath('items.0.price', 9500)
            ->assertJsonPath('items.1.category', 'Phones');

        $this->getJson('/listings?category=Cameras')
            ->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'Canon EOS camera');

        $this->getJson('/listings?min_price=20000&max_price=10000')
            ->assertStatus(422);

        $this->getJson('/listings?category=Electronics&sort=price_high')
            ->assertOk()->assertJsonPath('items.0.title', 'ThinkPad laptop');
    }

    public function test_legacy_photos_restore_only_for_existing_matching_facebook_listings(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'test-photo-a', 'title' => 'Redmi Note 15', 'price' => 6500,
                'city' => 'Tagbilaran', 'url' => 'https://www.facebook.com/marketplace/item/12345/'],
            ['id' => 'test-photo-b', 'title' => 'Another phone', 'price' => 8000,
                'city' => 'Dauis', 'url' => 'https://www.facebook.com/marketplace/item/98765/'],
        ]);

        $this->postJson('/listings/restore-images', ['images' => [
            ['listing_url' => 'https://www.facebook.com/marketplace/item/12345',
             'image_url' => 'https://scontent.xx.fbcdn.net/redmi.jpg'],
            ['listing_url' => 'https://www.facebook.com/marketplace/item/98765/',
             'image_url' => 'https://untrusted.example/image.png'],
            ['listing_url' => 'https://www.facebook.com/marketplace/item/55555/',
             'image_url' => 'https://scontent.xx.fbcdn.net/fake.jpg'],
        ]])->assertOk()->assertJsonPath('updated', 1);

        $this->getJson('/listings?q=redmi')->assertOk()
            ->assertJsonPath('items.0.image_url', 'https://scontent.xx.fbcdn.net/redmi.jpg');
        $this->assertDatabaseCount('discovered_listings', 2);
    }


    public function test_legacy_device_kind_aliases_include_xiaomi_and_camera(): void
    {
        $archive = app(ListingArchiveService::class);
        $cases = [
            'Xiaomi 14T 12/256GB' => 'Phones',
            'Infinix Note 50 Pro 5G' => 'Phones',
            'Tecno Camon 20 Pro' => 'Phones',
            'Canon EOS R50 mirrorless' => 'Cameras',
            'Sony ZV-E10 camera' => 'Cameras',
            'Lenovo Legion gaming laptop' => 'Laptops',
            'Nintendo Switch OLED' => 'Consoles',
            'RTX 4060 8GB' => 'PC Parts',
        ];
        foreach ($cases as $title => $expected) {
            $this->assertSame($expected, $archive->category($title), $title);
        }
    }

    public function test_camera_and_xiaomi_titles_remain_filterable_after_archiving(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'fixture-xiaomi', 'title' => 'Xiaomi 14T', 'price' => 10000, 'city' => 'Dauis', 'url' => null],
            ['id' => 'fixture-camera', 'title' => 'Canon EOS R50 Camera', 'price' => 20000, 'city' => 'Tagbilaran', 'url' => null],
        ]);
        $this->getJson('/listings?category=Phones&q=xiaomi')->assertOk()
            ->assertJsonCount(1, 'items')->assertJsonPath('items.0.category', 'Phones');
        $this->getJson('/listings?category=Cameras')->assertOk()
            ->assertJsonCount(1, 'items')->assertJsonPath('items.0.category', 'Cameras');
    }

    public function test_legacy_intent_filter_hides_phone_cases_and_buying_requests_without_deleting_archive(): void
    {
        app(ListingArchiveService::class)->store([
            ['id' => 'whole-phone', 'title' => 'iPhone 14 Pro Max rush sale', 'price' => 8500, 'city' => 'Panglao', 'url' => null],
            ['id' => 'case-ad', 'title' => 'iPhone 16 promax Cases', 'price' => 1500, 'city' => 'Tagbilaran', 'url' => null],
            ['id' => 'buyer', 'title' => 'LF budget phone', 'price' => 3000, 'city' => 'Dauis', 'url' => null],
            ['id' => 'repair', 'title' => 'iPhone repair service', 'price' => 1000, 'city' => 'Dauis', 'url' => null],
            ['id' => 'ram', 'title' => 'Laptop Ram DDR4 8gb 3200mhz', 'price' => 2000, 'city' => 'Panglao', 'url' => null],
        ]);

        $this->assertDatabaseCount('discovered_listings', 5);
        $this->assertDatabaseHas('discovered_listings', ['title' => 'iPhone 16 promax Cases', 'listing_intent' => 'accessory']);
        $this->assertDatabaseHas('discovered_listings', ['title' => 'iPhone repair service', 'listing_intent' => 'repair']);
        $this->getJson('/listings?category=Phones')->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'iPhone 14 Pro Max rush sale')
            ->assertJsonPath('diagnostics.hidden_non_sale', 3);
        $this->getJson('/listings?category=Electronics')->assertOk()->assertJsonCount(2, 'items');
    }


}
