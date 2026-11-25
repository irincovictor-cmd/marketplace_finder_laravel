<?php

namespace Tests\Feature;

use App\Services\ApifyCredentialStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApifySettingsTest extends TestCase
{
    use RefreshDatabase;

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
        $this->postJson('http://example.com/marketplace/api-key', ['token' => 'apify_api_exampleSecret123456789'])
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

    public function test_connection_check_is_private_and_never_runs_an_actor(): void
    {
        $token = 'apify_api_testConnection123456789';
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/v2/users/me' => Http::sequence()->push(['data' => ['id' => 'test']], 200)->push([], 401)]);
        $this->postJson('/marketplace/api-key/test')->assertOk()->assertJsonPath('status', 'missing');
        Http::assertNothingSent();
        $this->postJson('/marketplace/api-key', ['token' => $token])->assertOk();
        $this->postJson('http://example.com/marketplace/api-key/test')->assertForbidden();
        $this->postJson('/marketplace/api-key/test')->assertOk()->assertJsonPath('status', 'valid')->assertDontSee($token);
        $this->postJson('/marketplace/api-key/test')->assertOk()->assertJsonPath('status', 'invalid')->assertDontSee($token);
        Http::assertSentCount(2);
        Http::assertSent(fn ($req) => $req->url() === 'https://api.apify.com/v2/users/me' && $req->hasHeader('Authorization', 'Bearer '.$token));
    }

    public function test_public_connection_check_returns_no_account_data_or_secret(): void
    {
        $token = 'apify_api_demoConnection123456789';
        app(ApifyCredentialStore::class)->save($token);
        Http::preventStrayRequests();
        Http::fake(['https://api.apify.com/v2/users/me' => Http::response(['data' => ['email' => 'private@example.com', 'token' => $token]], 200)]);
        $this->postJson('/marketplace/tester-connection')->assertOk()
            ->assertJsonPath('status', 'valid')->assertDontSee($token)->assertDontSee('private@example.com');
        Http::assertSentCount(1);
        Http::assertSent(fn ($req) => $req->method() === 'GET' && $req->url() === 'https://api.apify.com/v2/users/me');
    }

    public function test_remote_owner_credentials_require_access_code_and_session(): void
    {
        $proxy = ['X-Marketplace-Demo-Proxy' => '1'];
        $token = 'apify_api_remoteOwner123456789';
        $this->get('/settings', $proxy)->assertRedirect('/demo-access');
        $this->postJson('/marketplace/api-key', ['token' => $token], $proxy)->assertForbidden();
        $this->postJson('/marketplace/scan', [], $proxy)->assertForbidden();
        $code = \App\Services\RemoteDemoAccess::code();
        $this->get('/demo-access', $proxy)->assertOk()->assertDontSee($code);
        $this->get('/demo-access')->assertOk()->assertSee($code);
        $this->post('/demo-access', ['code' => 'wrong'], $proxy)->assertForbidden();
        $this->post('/demo-access', ['code' => $code], $proxy)->assertRedirect('/discover');
        $this->get('/browse', $proxy)->assertRedirect('/discover');
        $this->get('/settings', $proxy)->assertOk()->assertSee('Test saved API key');
        $this->postJson('/marketplace/api-key', ['token' => $token], $proxy)->assertOk()->assertDontSee($token);
        $this->post('/demo-access/logout', [], $proxy)->assertRedirect('/browse');
        $this->deleteJson('/marketplace/api-key', [], $proxy)->assertForbidden();
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
