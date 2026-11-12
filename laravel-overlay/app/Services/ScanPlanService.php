<?php

namespace App\Services;

class ScanPlanService
{
    // Explicitly selected scan breadth, not an exhaustive Facebook feed.
    // Each term is a separate potentially billable Apify run.
    private const POOLS = [
        'Phones' => ['phone', 'redmi', 'iphone', 'samsung', 'poco', 'realme'],
        'Laptops' => ['laptop', 'notebook', 'asus', 'lenovo', 'acer', 'macbook'],
        'Cameras' => ['camera', 'canon', 'nikon', 'sony camera', 'fujifilm', 'gopro'],
        'Consoles' => ['ps5', 'switch', 'xbox', 'playstation', 'ps4', 'steam deck'],
        'PC Parts' => ['rtx', 'ryzen', 'graphics card', 'ssd', 'monitor', 'motherboard'],
        'Electronics' => ['phone', 'laptop', 'camera', 'speaker', 'console', 'monitor'],
        'Other' => ['electronics', 'gadget', 'speaker', 'tablet', 'smartwatch', 'headset'],
        'All' => ['phone', 'laptop', 'camera', 'speaker', 'console', 'monitor'],
    ];

    public function terms(?string $keyword, ?string $category, string $coverage = 'quick'): array
    {
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            return [$keyword];
        }
        $limits = ['quick' => 2, 'balanced' => 4, 'broad' => 6];
        $pool = self::POOLS[$category ?: 'All'] ?? self::POOLS['All'];
        return array_slice($pool, 0, $limits[$coverage] ?? 2);
    }

    public function preview(?string $keyword, ?string $category, string $coverage = 'quick'): array
    {
        $terms = $this->terms($keyword, $category, $coverage);
        return [
            'terms' => $terms,
            'requests' => count($terms),
            'coverage' => $coverage,
            'coverage_note' => 'Each keyword runs a limited Facebook search (one provider page, up to 60 results, last 7 days). These are samples, not the complete category inventory. Only Bohol-evidenced results appear as deals.',
            'billing_note' => 'Every term starts a separate potentially billable Apify run. More terms may cost more; the per-run charge parameter is not a guaranteed spending cap.',
        ];
    }
}
