<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketplaceCalculatorTest extends TestCase
{
    public function test_dashboard_renders_a_laravel_blade_view(): void
    {
        $this->get('/')->assertOk()->assertSee('Find your next great deal')
            ->assertSee('id="historySearchButton"', false)
            ->assertSee('Set up a fresh search')
            ->assertSee('Fresh Marketplace search')
            ->assertSee('Activity logs')
            ->assertSee('id="logExport"', false)
            ->assertSee('data-view="activity"', false)
            ->assertSee('id="view-activity"', false);
    }

    public function test_scan_form_is_captured_before_async_plan_request(): void
    {
        // Browser event.currentTarget is reset after async listener yields.
        // Prevent a regression where FormData receives a cleared event target.
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('const scanForm=e.currentTarget;', $blade);
        $this->assertStringContainsString('const approvedData=Object.fromEntries(new FormData(scanForm));', $blade);
        $this->assertStringNotContainsString('new FormData(e.currentTarget)', $blade);
        $this->assertStringNotContainsString('e.currentTarget.reset()', $blade);
        $this->assertStringContainsString('const form=e.currentTarget;', $blade);
        $this->assertStringContainsString('form.reset();', $blade);
    }

    public function test_dashboard_explains_scan_rate_limit_without_auto_retry(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('response.status===429', $blade);
        $this->assertStringContainsString('scan_rate_limited', $blade);
        $this->assertStringContainsString('Retry after approximately ', $blade);
    }

    public function test_scan_failure_stays_on_discovery_screen(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString("document.getElementById('scanSettings').open=true;", $blade);
        $this->assertStringNotContainsString('  openActivityLogs();', $blade);
        $this->assertStringContainsString("status.setAttribute('role','alert');", $blade);
    }

    public function test_scan_has_no_browser_cooldown_and_still_prevents_duplicate_clicks(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringNotContainsString('scanCooldownMessage', $blade);
        $this->assertStringNotContainsString('startScanCooldown(', $blade);
        $this->assertStringContainsString('if(scanInFlight)return;', $blade);
    }

    public function test_invalid_budget_clears_previous_cards_and_scan_breakdown_is_visible(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('No search performed: minimum price exceeds maximum price.', $blade);
        $this->assertStringContainsString('unique normalized listings', $blade);
        $this->assertStringContainsString('hidden_non_sale', $blade);
    }

    public function test_rejected_listings_are_a_dedicated_sidebar_inspector(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-view="rejected"', false)
            ->assertSee('id="view-rejected"', false)
            ->assertSee('id="rejectedScope"', false)
            ->assertSee('id="rejectedReason"', false)
            ->assertSee('id="rejectedResults"', false)
            ->assertSee('id="rejectedNext"', false);
    }

    public function test_manual_availability_sidebar_and_card_controls_render(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-view="status"', false)
            ->assertSee('id="view-status"', false)
            ->assertSee('id="manualHistoryEntries"', false)
            ->assertSee('makeManualStatusControl(', false)
            ->assertSee('Availability history');
    }

    public function test_secure_apify_key_form_renders_without_embedding_a_secret(): void
    {
        config(['marketplace.apify_token' => 'private-server-key-not-for-html']);
        $this->get('/')->assertOk()
            ->assertSee('id="apiKeySettings"', false)
            ->assertSee('id="apiKeyInput"', false)
            ->assertSee('type="password"', false)
            ->assertSee('Save / replace key')
            ->assertDontSee('private-server-key-not-for-html');
    }

    public function test_watchlist_matching_price_fields_and_listing_pagination_are_on_dashboard(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('id="watchMatchesPanel"', false)
            ->assertSee('id="watchMatchNext"', false)
            ->assertSee('id="listingPager"', false)
            ->assertSee('id="listingNext"', false)
            ->assertSee('data-money="true"', false)
            ->assertSee('inputmode="decimal"', false);

        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('function formatPriceText(value)', $blade);
        $this->assertStringContainsString('function formatPriceField(el)', $blade);
        $this->assertStringContainsString("moneyRaw(payload[k])", $blade);
        $this->assertStringContainsString("loadWatchMatches(", $blade);
    }

    public function test_named_clean_dashboard_routes_are_directly_accessible(): void
    {
        $paths = [
            '/discover' => 'Find your next great deal',
            '/saved-items' => 'Saved items',
            '/watchlist' => 'Your watchlist',
            '/profit-calculator' => 'Profit calculator',
            '/activity-logs' => 'Activity logs',
            '/rejected-listings' => 'Rejected listings',
            '/availability-history' => 'Availability history',
            '/settings' => 'Settings',
        ];
        foreach ($paths as $path => $content) {
            $this->get($path)->assertOk()->assertSee($content);
        }
        $this->get('/')->assertOk();
    }

    public function test_sidebar_uses_clean_laravel_urls_and_supports_history(): void
    {
        $this->get('/discover')->assertOk()
            ->assertSee('href="/watchlist"', false)
            ->assertSee('href="/saved-items"', false)
            ->assertSee('href="/rejected-listings"', false)
            ->assertDontSee('href="#watchlist"', false);

        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('history.pushState({view}', $blade);
        $this->assertStringContainsString("window.addEventListener('popstate'", $blade);
        $this->assertStringContainsString('location.hash.slice(1)', $blade);
    }

    public function test_old_ip_dashboard_address_redirects_to_localhost(): void
    {
        $this->get('http://127.0.0.1:8000/discover')
            ->assertRedirect('http://localhost:8000/discover');
        $this->get('http://127.0.0.1:8000/watchlist')
            ->assertRedirect('http://localhost:8000/watchlist');
        $this->get('http://127.0.0.1:8000/')
            ->assertRedirect('http://localhost:8000/');
        $this->get('http://localhost:8000/discover')->assertOk();
        $this->get('http://localhost:8000/watchlist')->assertOk();
    }

    public function test_dashboard_has_client_fallback_for_old_address_and_hash_links(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString("location.hostname === '127.0.0.1'", $blade);
        $this->assertStringContainsString("location.replace('http://localhost:8000'", $blade);
        $this->assertStringContainsString('location.hash.slice(1)', $blade);
    }

    public function test_route_overlay_is_deployed_by_bootstrap_after_clean_route_revisions(): void
    {
        $bootstrap = file_get_contents('/opt/marketplace/bootstrap.sh');
        $this->assertStringContainsString("grep -Fq '\\$dashboardPages = [' routes/web.php", $bootstrap);
        $this->assertStringContainsString('MARKETPLACE_FINDER_MANAGED_ROUTES', $bootstrap);
        $this->assertStringContainsString(
            'cp /opt/marketplace/laravel-overlay/routes/web.php routes/web.php',
            $bootstrap
        );
    }

    public function test_api_key_has_dedicated_settings_route_and_scan_is_above_archive(): void
    {
        $this->get('/settings')->assertOk()
            ->assertSee('id="view-settings"', false)
            ->assertSee('href="/settings"', false)
            ->assertSee('id="apiKeySettings"', false)
            ->assertSee('id="apiKeyInput"', false)
            ->assertSee('id="apiKeyRemove"', false);

        $html = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertSame(1, substr_count($html, 'id="scanSettings"'));
        $this->assertSame(1, substr_count($html, 'id="apiKeySettings"'));
        $this->assertTrue(strpos($html, 'id="scanSettings"') < strpos($html, 'id="listingResults"'));
        $this->assertTrue(strpos($html, 'id="listingResults"') < strpos($html, 'id="view-settings"'));
        $this->assertTrue(strpos($html, 'id="view-settings"') < strpos($html, 'id="apiKeySettings"'));
        $this->assertStringContainsString("settings:'/settings'", $html);
    }

    public function test_profit_and_roi_are_computed_on_server(): void
    {
        $this->postJson('/calculator/estimate', [
            'buying_price' => 8000,
            'selling_price' => 10000,
            'extra_costs' => 500,
        ])->assertOk()->assertJsonPath('profit', 1500)->assertJsonPath('roi_percent', 18.75);
    }

    public function test_negative_cost_is_rejected(): void
    {
        $this->postJson('/calculator/estimate', [
            'buying_price' => 8000,
            'selling_price' => 10000,
            'extra_costs' => -1,
        ])->assertUnprocessable()->assertJsonValidationErrors('extra_costs');
    }

    public function test_zero_buying_price_is_rejected(): void
    {
        $this->postJson('/calculator/estimate', [
            'buying_price' => 0,
            'selling_price' => 10000,
            'extra_costs' => 500,
        ])->assertUnprocessable()->assertJsonValidationErrors('buying_price');
    }
}
