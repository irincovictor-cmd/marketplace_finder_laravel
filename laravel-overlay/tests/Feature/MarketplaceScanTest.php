<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceScanTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'keyword' => 'iphone',
            'latitude' => 9.6477,
            'longitude' => 123.8547,
            'radius_km' => 40,
            'sort_by' => 'creation_time_descend',
            'confirm_paid_scan' => true,
        ], $overrides);
    }

    public function test_scanning_is_disabled_by_default_and_does_not_call_provider(): void
    {
        config(['marketplace.scan_enabled' => false, 'marketplace.apify_token' => 'fake-test-token']);
        Http::preventStrayRequests();
        Http::fake();
        $this->postJson('/marketplace/scan', $this->payload())->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_request_must_have_explicit_paid_scan_confirmation(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::fake();
        $this->postJson('/marketplace/scan', $this->payload(['confirm_paid_scan' => false]))
            ->assertUnprocessable()->assertJsonValidationErrors('confirm_paid_scan');
        Http::assertNothingSent();
    }

    public function test_missing_token_disables_scan_without_request(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => '']);
        Http::fake();
        $this->postJson('/marketplace/scan', $this->payload())->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_approved_scan_uses_authorization_header_and_returns_normalized_items(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/*' => Http::response([
            [
                'id' => 'abc123',
                'marketplace_listing_title' => 'iPhone 13',
                'listing_price' => ['amount' => 12500],
                'location' => ['city' => 'Tagbilaran'],
                'listingUrl' => 'https://www.facebook.com/marketplace/item/123',
            ],
            [
                'id' => 'bad',
                'marketplace_listing_title' => 'Accessory',
                'listing_price' => ['amount' => 500],
                'listingUrl' => 'https://malicious.example/link',
            ],
        ], 200)]);
        $this->postJson('/marketplace/scan', $this->payload())
            ->assertOk()
            ->assertJsonPath('returned', 2)
            ->assertJsonPath('listings.0.price', 12500)
            ->assertJsonPath('listings.0.url', 'https://www.facebook.com/marketplace/item/123')
            ->assertJsonPath('listings.1.url', null);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://api.apify.com/')
                && ! str_contains($request->url(), 'fake-test-token')
                && $request->hasHeader('Authorization', 'Bearer fake-test-token')
                && $request['searchKeyword'] === 'iphone'
                && $request['daysSinceListed'] === '7'
                && $request['maxPagesPerUrl'] === 1
                && $request['latitude'] === 9.6477;
        });
    }

    public function test_provider_failure_does_not_expose_secret(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::fake(['https://api.apify.com/*' => Http::response(['message' => 'fake-test-token'], 500)]);
        $this->postJson('/marketplace/scan', $this->payload())
            ->assertStatus(502)->assertDontSee('fake-test-token');
    }

    public function test_invalid_coordinates_are_rejected_before_provider_call(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::fake();
        $this->postJson('/marketplace/scan', $this->payload(['latitude' => 999]))
            ->assertUnprocessable()->assertJsonValidationErrors('latitude');
        Http::assertNothingSent();
    }
    public function test_keyword_free_broad_scan_passes_optional_price_and_sort_to_actor(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/*' => Http::response([], 200)]);

        $params = $this->payload([
            'keyword' => '',
            'category' => 'Electronics',
            'min_price' => 2000,
            'max_price' => 20000,
            'sort_by' => 'price_ascend',
        ]);
        $this->postJson('/marketplace/scan', $params)
            ->assertOk()->assertJsonPath('archived_count', 0)
            ->assertJsonPath('category', 'Electronics');

        Http::assertSent(function ($request) {
            return ! array_key_exists('searchKeyword', $request->data())
                && $request['minPrice'] === 2000
                && $request['maxPrice'] === 20000
                && $request['sortBy'] === 'price_ascend';
        });
    }

    public function test_scan_rejects_inverted_price_range_before_any_billable_request(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::fake();
        $this->postJson('/marketplace/scan', $this->payload([
            'min_price' => 22000, 'max_price' => 10000,
        ]))->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_marketplace_photo_is_returned_and_unsafe_urls_are_ignored(): void
    {
        config(['marketplace.scan_enabled' => true, 'marketplace.apify_token' => 'fake-test-token']);
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/*' => Http::response([
            [
                'id' => 'photo-1',
                'marketplace_listing_title' => 'Canon EOS Camera',
                'listing_price' => ['amount' => 15000],
                'location' => ['reverse_geocode' => ['city' => 'Tagbilaran City', 'state' => 'Bohol']],
                'primary_listing_photo_url' => 'https://scontent.xx.fbcdn.net/example-product.jpg',
            ],
            [
                'id' => 'photo-2',
                'marketplace_listing_title' => 'Laptop',
                'listing_price' => ['amount' => 23000],
                'location' => ['reverse_geocode' => ['city' => 'Dauis', 'state' => 'Bohol']],
                'primary_listing_photo_url' => 'https://malicious.example/collect.jpg',
            ],
        ], 200)]);

        $this->postJson('/marketplace/scan', $this->payload())
            ->assertOk()
            ->assertJsonPath('listings.0.image_url', 'https://scontent.xx.fbcdn.net/example-product.jpg')
            ->assertJsonPath('listings.1.image_url', null);

        $this->getJson('/listings?category=Cameras')->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.image_url', 'https://scontent.xx.fbcdn.net/example-product.jpg');
    }

}
