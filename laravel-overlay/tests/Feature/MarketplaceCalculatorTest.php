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
    }

    public function test_dashboard_explains_scan_rate_limit_without_auto_retry(): void
    {
        $blade = file_get_contents(resource_path('views/marketplace/dashboard.blade.php'));
        $this->assertStringContainsString('response.status===429', $blade);
        $this->assertStringContainsString('scan_rate_limited', $blade);
        $this->assertStringContainsString('Wait about ', $blade);
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
