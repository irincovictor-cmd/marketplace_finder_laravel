<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketplaceCalculatorTest extends TestCase
{
    public function test_dashboard_renders_a_laravel_blade_view(): void
    {
        $this->get('/')->assertOk()->assertSee('Find your next great deal')
            ->assertSee('Search saved results')
            ->assertSee('Get fresh results')
            ->assertSee('Fresh Marketplace search');
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
