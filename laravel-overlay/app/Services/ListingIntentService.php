<?php

namespace App\Services;

/**
 * A conservative port of legacy classify.js's sale-intent gate.
 * Preserve records in the archive; exclude only clear buying requests,
 * repair-service advertisements and phone accessories from deal results.
 * Unknown or defective whole devices remain reviewable, as in FlipRadar.
 */
class ListingIntentService
{
    public function assess(string $title, string $category): array
    {
        $t = mb_strtolower(trim($title));
        $selling = preg_match('/\b(for sale|selling|sell|rush|available|mupalit|brand\s?new|bnew)\b/u', $t) === 1;
        if (preg_match('/\b(wtb|wanted|looking for|we buy|i buy|buying|binibili|bumibili|bilhan|paliton|iso|palit ko|pm sa naay)\b/u', $t)
            && ! preg_match('/\bfor sale\b/u', $t)) {
            return ['status' => 'wanted', 'reason' => 'Buying request wording'];
        }
        if (preg_match('/\b(repair(?:s|ing)?\s*(?:center|centre|shop|service|services|store)|(?:gadget|phone|cellphone|iphone|mobile|laptop)\s+repair|we repair|pa-?repair|screen replacement service|unlocking|unlock service|icloud\s*(?:unlock|bypass|removal)|flashing|jailbreak)\b/u', $t)) {
            return ['status' => 'repair', 'reason' => 'Repair or unlock service wording'];
        }
        if (preg_match('/\brepair(?:s|ing)?\b/u', $t) && ! $selling
            && ! preg_match('/\b(for repair|needs? repair)\b/u', $t)) {
            return ['status' => 'repair', 'reason' => 'Repair wording without sale cues'];
        }

        // Legacy's phone accessory rules only apply to phone listings.
        // RAM, CPUs, SSDs and other individual PC parts remain legitimate
        // PC Parts items, not wrongly rejected phone accessories.
        if ($category === 'Phones') {
            $deviceSpec = preg_match('/\b(?:16|32|64|128|256|512|1024)\s?gb\b|\b(?:4|6|8|12|16)\s?[+\/]\s?(?:64|128|256|512)\b/u', $t);
            $phoneAccessory = preg_match('/\b(cases?|casings?|covers?|chargers?|charging cables?|screen protectors?|tempered glass|housing|replacement lcd|back covers?|flex cables?|sim trays?|pouches?)\b/u', $t);
            $deviceWithAccessory = preg_match('/\b(with|w\/|free|inclusive|complete)\b/u', $t)
                && preg_match('/\b(iphone|samsung|galaxy|xiaomi|redmi|poco|oppo|realme|vivo)\b/u', $t);
            if ($phoneAccessory && ! $deviceSpec && ! $deviceWithAccessory) {
                return ['status' => 'accessory', 'reason' => 'Phone accessory or replacement part'];
            }
        }
        return ['status' => 'reviewable', 'reason' => 'Sale or uncertain item'];
    }
}
