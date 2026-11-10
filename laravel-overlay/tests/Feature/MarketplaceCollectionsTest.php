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
}
