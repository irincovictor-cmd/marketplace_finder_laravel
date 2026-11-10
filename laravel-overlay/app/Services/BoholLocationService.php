<?php

namespace App\Services;

/**
 * Conservative Bohol-only gate based on FlipRadar's proven province/town logic.
 * PH-07 is Central Visayas, NOT evidence of Bohol. Unknown/ambiguous
 * records remain unverified and must not be shown as local.
 */
class BoholLocationService
{
    private const AMBIGUOUS = ['valencia', 'carmen', 'danao', 'alicia', 'anda', 'buenavista', 'pilar', 'san isidro', 'san miguel', 'talisay'];
    private const CEBU_TOWNS = ['cebu', 'mandaue', 'lapu lapu', 'carcar', 'cordova', 'consolacion', 'oslob', 'minglanilla', 'naga', 'toledo', 'dumanjug', 'barili', 'sibonga', 'argao', 'dalaguete', 'alcoy', 'boljoon', 'santander', 'moalboal', 'badian', 'aloguinsan', 'balamban', 'asturias', 'liloan', 'compostela'];

    private const BOHOL_TOWNS = ['Alburquerque','Alicia','Anda','Antequera','Baclayon','Balilihan','Batuan','Bien Unido','Bilar','Buenavista','Calape','Candijay','Carmen','Catigbian','Clarin','Corella','Cortez','Dagohoy','Danao','Dauis','Dimiao','Duero','Garcia-Hernandez','Guindulman','Inabanga','Jagna','Jetafe','Lila','Loay','Loboc','Loon','Mabini','Maribojoc','Panglao','Pilar','Pres. Cp Garcia (Pitogo)','Sagbayan','San Isidro','San Miguel','Sevilla','Sierra  Bullones','Sikatuna','Tagbilaran City','Talibon','Trinidad','Tubigon','Ubay','Valencia']; 

    public function assess(?string $city, ?string $province = null, ?string $locationText = null, ?float $latitude = null, ?float $longitude = null): array
    {
        $cityName = $this->normalize($city);
        $provinceText = mb_strtolower(trim($province ?? ''));
        $text = mb_strtolower(trim($locationText ?? ''));
        $statedBohol = preg_match('/\bbohol\b/u', $provinceText.' '.$text) === 1;
        $otherProvince = preg_match('/\b(cebu|negros|leyte|siquijor|mindanao|luzon)\b/u', $provinceText.' '.$text) === 1;

        // Direct contradictions always win over a plausible town name.
        if ($otherProvince) return ['status' => 'outside', 'reason' => 'Other province stated'];
        if (in_array($cityName, self::CEBU_TOWNS, true) || str_contains($cityName, 'cebu')) {
            return ['status' => 'outside', 'reason' => 'Cebu municipality stated'];
        }
        $knownTown = in_array($cityName, array_map([$this, 'normalize'], self::BOHOL_TOWNS), true);
        if (in_array($cityName, self::AMBIGUOUS, true) && ! $statedBohol) {
            return ['status' => 'verify', 'reason' => 'Town name occurs in several provinces'];
        }

        // Coordinates are corroborative only: the rectangle is not a province polygon.
        $hasCoords = $latitude !== null && $longitude !== null;
        if ($hasCoords && ! ($latitude >= 9.50 && $latitude <= 10.20 && $longitude >= 123.65 && $longitude <= 124.65)) {
            return ['status' => 'outside', 'reason' => 'Coordinates outside conservative Bohol bounds'];
        }

        if ($knownTown || $statedBohol) {
            return ['status' => 'bohol', 'reason' => $statedBohol ? 'Bohol stated' : 'Recognized Bohol municipality'];
        }
        return ['status' => 'verify', 'reason' => 'Missing reliable Bohol locality evidence'];
    }

    private function normalize(?string $town): string
    {
        $town = mb_strtolower(trim($town ?? ''));
        $town = str_replace(['-', '.', '  '], ' ', $town);
        $town = preg_replace('/^city of\s+|\s+city$/u', '', $town);
        $town = preg_replace('/[^a-z0-9 ]+/u', ' ', $town);
        $town = trim(preg_replace('/\s+/u', ' ', $town));
        return match ($town) {
            'tagbilaran' => 'tagbilaran',
            'cortes' => 'cortez',
            'getafe' => 'jetafe',
            'president carlos p garcia', 'pres cp garcia pitogo' => 'pres cp garcia pitogo',
            default => $town,
        };
    }
}
