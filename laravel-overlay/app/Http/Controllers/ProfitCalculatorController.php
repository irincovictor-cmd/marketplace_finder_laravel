<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitCalculatorController extends Controller
{
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'buying_price' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'extra_costs' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);

        // Integer centavos avoid binary floating-point rounding drift for money.
        $buying = (int) round((float) $data['buying_price'] * 100);
        $selling = (int) round((float) $data['selling_price'] * 100);
        $extra = (int) round((float) $data['extra_costs'] * 100);
        $profit = $selling - $buying - $extra;

        return response()->json([
            'buying_price' => $buying / 100,
            'selling_price' => $selling / 100,
            'extra_costs' => $extra / 100,
            'profit' => $profit / 100,
            'roi_percent' => round($profit / $buying * 100, 2),
            'is_profitable' => $profit > 0,
            'disclaimer' => 'Manual estimate only. No marketplace resale value is verified.',
        ]);
    }
}
