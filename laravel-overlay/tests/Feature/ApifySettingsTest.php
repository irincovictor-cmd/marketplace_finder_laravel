<?php

namespace Tests\Feature;

use App\Services\ApifyCredentialStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApifySettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local'); // Never write a real credential during tests.
        config(['marketplace.apify_token' => '', 'marketplace.scan_enabled' => false]);
    }

    public function test_key_can_be_pasted_encrypted_and_replaced_without_leaking_to_ui(): void
    {
        $token = 'apify_api_exampleSecret123456789';
        $other = 'apify_api_newSecret123456789';
        $this->getJson('/marketplace/api-key')
            ->assertOk()->assertJsonPath('configured', false)->assertDontSee($token);

        $this->postJson('/marketplace/api-key', ['token' => $token])
            ->assertOk()->assertJsonPath('configured', true)
            ->assertJsonPath('scan_enabled', true)->assertDontSee($token);

        Storage::disk('local')->assertExists('private/marketplace/apify-token.enc');
        $raw = Storage::disk('local')->get('private/marketplace/apify-token.enc');
        $this->assertStringNotContainsString($token, $raw);
        $this->assertSame($token, app(ApifyCredentialStore::class)->getToken());
        $this->getJson('/marketplace/api-key')
            ->assertOk()->assertJsonPath('source', 'encrypted_file')->assertDontSee($token);

        $this->postJson('/marketplace/api-key', ['token' => $other])->assertOk()->assertDontSee($other);
        $this->assertSame($other, app(ApifyCredentialStore::class)->getToken());
        $this->assertStringNotContainsString($other, Storage::disk('local')->get('private/marketplace/apify-token.enc'));

        $this->deleteJson('/marketplace/api-key')->assertOk()->assertJsonPath('configured', false);
        Storage::disk('local')->assertMissing('private/marketplace/apify-token.enc');
    }

    public function test_invalid_key_and_nonlocal_host_are_rejected(): void
    {
        $this->postJson('/marketplace/api-key', ['token' => 'not-a-token'])
            ->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->withServerVariables(['HTTP_HOST' => 'example.com'])
            ->postJson('/marketplace/api-key', ['token' => 'apify_api_exampleSecret123456789'])
            ->assertForbidden();
        Storage::disk('local')->assertMissing('private/marketplace/apify-token.enc');
    }

    public function test_saved_key_enables_only_explicitly_approved_scan_with_correct_authorization(): void
    {
        $token = 'apify_api_savedTest123456789';
        $this->postJson('/marketplace/api-key', ['token' => $token])->assertOk();

        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/*' => Http::response([], 200)]);
        $payload = [
            'keyword' => 'redmi', 'latitude' => 9.6477, 'longitude' => 123.8547,
            'radius_km' => 40, 'sort_by' => 'creation_time_descend',
        ];
        $this->postJson('/marketplace/scan', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('confirm_paid_scan');
        Http::assertNothingSent();

        $this->postJson('/marketplace/scan', [...$payload, 'confirm_paid_scan' => true])
            ->assertOk()->assertJsonPath('completed_requests', 1)->assertDontSee($token);
        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer '.$token)
            && ! str_contains($req->url(), $token));
    }

    public function test_environment_token_remains_compatible_without_browser_vault(): void
    {
        config(['marketplace.apify_token' => 'legacy-env-token']);
        $this->getJson('/marketplace/api-key')->assertOk()
            ->assertJsonPath('configured', true)->assertJsonPath('source', 'environment')
            ->assertDontSee('legacy-env-token');
        $this->assertSame('legacy-env-token', app(ApifyCredentialStore::class)->getToken());
    }
}
