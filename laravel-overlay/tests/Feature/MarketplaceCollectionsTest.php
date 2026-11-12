<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCollectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_listing_can_be_saved_read_updated_and_deleted(): void
    {
        $payload = [
            'title' => 'iPhone 13',
            'listing_url' => 'https://www.facebook.com/marketplace/item/123456/',
            'price' => 12500,
            'city' => 'Tagbilaran',
        ];

        $response = $this->postJson('/collections/saved', $payload)->assertCreated();
        $id = $response->json('item.id');

        $this->getJson('/collections/saved')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'iPhone 13');

        $this->postJson('/collections/saved', [...$payload, 'title' => 'iPhone 13 updated'])
            ->assertOk()->assertJsonPath('item.id', $id);

        $this->assertDatabaseCount('saved_listings', 1);
        $this->deleteJson('/collections/saved/'.$id)->assertOk();
        $this->assertDatabaseCount('saved_listings', 0);
    }

    public function test_invalid_saved_url_is_rejected(): void
    {
        $this->postJson('/collections/saved', [
            'title' => 'Bad URL',
            'listing_url' => 'https://facebook.com.evil.example/marketplace/item/12',
        ])->assertUnprocessable()->assertJsonValidationErrors('listing_url');

        $this->postJson('/collections/saved', [
            'title' => 'Bad URL',
            'listing_url' => 'http://www.facebook.com/marketplace/item/12',
        ])->assertUnprocessable()->assertJsonValidationErrors('listing_url');
    }

    public function test_watchlist_is_normalized_and_deduplicated(): void
    {
        $first = $this->postJson('/collections/watchlist', [
            'keyword' => '  iPHONE   13 ',
            'max_price' => 11000,
            'enabled' => true,
        ])->assertCreated();

        $this->postJson('/collections/watchlist', [
            'keyword' => 'iphone 13',
            'max_price' => 10000,
            'enabled' => false,
        ])->assertOk()->assertJsonPath('item.id', $first->json('item.id'));

        $this->getJson('/collections/watchlist')
            ->assertOk()->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.keyword', 'iphone 13')
            ->assertJsonPath('items.0.enabled', false);

        $this->deleteJson('/collections/watchlist/'.$first->json('item.id'))->assertOk();
        $this->assertDatabaseCount('watchlist_entries', 0);
    }

    public function test_watchlist_rejects_negative_budget_and_invalid_keywords(): void
    {
        $this->postJson('/collections/watchlist', [
            'keyword' => 'iphone', 'max_price' => -1, 'enabled' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('max_price');

        $this->postJson('/collections/watchlist', [
            'keyword' => '<script>', 'max_price' => 10000, 'enabled' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('keyword');
    }
    public function test_watchlist_matches_only_active_bohol_deals_within_budget_and_paginates(): void
    {
        $archive = app(\App\Services\ListingArchiveService::class);
        $archive->store([
            ['id' => 'watch-a', 'title' => 'POCO F7 12/256', 'price' => 10500, 'city' => 'Dauis', 'url' => null],
            ['id' => 'watch-b', 'title' => 'POCO F7 sealed', 'price' => 12500, 'city' => 'Panglao', 'url' => null],
            ['id' => 'watch-c', 'title' => 'POCO F7', 'price' => 9500, 'city' => 'Mandaue', 'province' => 'Cebu', 'url' => null],
            ['id' => 'watch-d', 'title' => 'POCO F7 case', 'price' => 500, 'city' => 'Tagbilaran', 'url' => null],
            ['id' => 'watch-e', 'title' => 'Redmi Note 14', 'price' => 8000, 'city' => 'Dauis', 'url' => null],
        ]);
        $watch = $this->postJson('/collections/watchlist', [
            'keyword' => 'poco f7', 'max_price' => 11000, 'enabled' => true,
        ])->assertCreated();
        $id = $watch->json('item.id');

        $this->getJson('/collections/watchlist')->assertOk()
            ->assertJsonPath('items.0.match_count', 1);
        $this->getJson('/collections/watchlist/'.$id.'/matches')->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('items.0.title', 'POCO F7 12/256')
            ->assertJsonPath('source', 'local_sqlite');

        $listing = \App\Models\DiscoveredListing::query()->where('title', 'POCO F7 12/256')->firstOrFail();
        $this->patchJson('/listings/'.$listing->id.'/manual-status', ['status' => 'sold'])->assertOk();
        $this->getJson('/collections/watchlist/'.$id.'/matches')->assertOk()->assertJsonPath('total', 0);

        $this->postJson('/collections/watchlist', [
            'keyword' => 'poco f7', 'max_price' => 11000, 'enabled' => false,
        ])->assertOk();
        $this->getJson('/collections/watchlist')->assertOk()->assertJsonPath('items.0.match_count', 0);
        $this->getJson('/collections/watchlist/'.$id.'/matches')->assertOk()->assertJsonPath('total', 0);
        $this->assertDatabaseCount('discovered_listings', 5);
    }

    public function test_watchlist_results_use_native_pagination_without_paid_requests(): void
    {
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake();
        $items = [];
        for ($i = 1; $i <= 17; $i++) {
            $items[] = ['id' => 'watch-page-'.$i, 'title' => 'iPhone 13 '.$i, 'price' => 8000,
                'city' => 'Dauis', 'url' => null];
        }
        app(\App\Services\ListingArchiveService::class)->store($items);
        $watch = $this->postJson('/collections/watchlist', [
            'keyword' => 'iphone 13', 'max_price' => 9000, 'enabled' => true,
        ])->assertCreated();
        $id = $watch->json('item.id');

        $this->getJson('/collections/watchlist/'.$id.'/matches?page=1')->assertOk()
            ->assertJsonCount(12, 'items')->assertJsonPath('total', 17)->assertJsonPath('pages', 2);
        $this->getJson('/collections/watchlist/'.$id.'/matches?page=2')->assertOk()
            ->assertJsonCount(5, 'items')->assertJsonPath('page', 2);
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function test_saved_and_watchlist_collections_paginate_like_the_original_laravel_project(): void
    {
        for ($i = 1; $i <= 23; $i++) {
            \App\Models\SavedListing::create([
                'title' => 'Listing '.$i,
                'listing_url' => 'https://www.facebook.com/marketplace/item/'.(1000+$i),
            ]);
            \App\Models\WatchlistEntry::create([
                'keyword' => 'device '.$i,
                'max_price' => 10000,
                'enabled' => true,
            ]);
        }

        $this->getJson('/collections/saved?page=1')->assertOk()
            ->assertJsonPath('total', 23)->assertJsonPath('pages', 2)->assertJsonCount(20, 'items');
        $this->getJson('/collections/saved?page=2')->assertOk()->assertJsonCount(3, 'items');
        $this->getJson('/collections/watchlist?page=1')->assertOk()
            ->assertJsonPath('total', 23)->assertJsonPath('pages', 2)->assertJsonCount(20, 'items');
        $this->getJson('/collections/watchlist?page=2')->assertOk()->assertJsonCount(3, 'items');
    }


}
