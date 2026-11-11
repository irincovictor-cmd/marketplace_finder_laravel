<?php

namespace App\Services;

class ScanPlanService
{
    // Small, bounded version of the legacy FlipRadar term pools.
    // These are discovery probes, NOT an exhaustive Facebook category feed.
    private const POOLS = [
        'Phones' => ['phone', 'redmi'],
        'Laptops' => ['laptop', 'notebook'],
        'Cameras' => ['camera', 'canon'],
        'Consoles' => ['ps5', 'switch'],
        'PC Parts' => ['rtx', 'ryzen'],
        'Electronics' => ['phone', 'laptop'],
        'Other' => ['electronics', 'gadget'],
        'All' => ['phone', 'laptop'],
    ];

    public function terms(?string $keyword, ?string $category): array
    {
        $keyword = trim((string) $keyword);
        if ($keyword !== '') {
            return [$keyword];
        }

        return self::POOLS[$category ?: 'All'];
    }

    public function preview(?string $keyword, ?string $category): array
    {
        $terms = $this->terms($keyword, $category);
        return [
            'terms' => $terms,
            'requests' => count($terms),
            'coverage_note' => 'These keyword probes are limited samples. They do not reproduce the full Facebook category feed or guarantee new Bohol results.',
            'billing_note' => 'Every term launches a separate potentially billable Apify actor run. The per-run actor charge parameter is not a guaranteed spending cap.',
        ];
    }
}
