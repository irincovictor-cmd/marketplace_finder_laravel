<?php

namespace App\Services;

class ListingPhotoCache
{
    public static function safeUrl(mixed $candidate): ?string
    {
        if (! is_string($candidate) || strlen($candidate) > 4096 || preg_match('/[\x00-\x20\x7f]/', $candidate)) return null;
        $parts = parse_url($candidate);
        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)) return null;
        $host = strtolower($parts['host'] ?? '');
        return preg_match('/^[a-z0-9.-]+$/D', $host) && ($host === 'fbcdn.net' || str_ends_with($host, '.fbcdn.net')
            || $host === 'fbsbx.com' || str_ends_with($host, '.fbsbx.com')) ? $candidate : null;
    }

    // Cache by listing identity rather than the expiring CDN signature.
    public function path(int $id): string
    {
        return storage_path('app/private/listing-photos/'.$id.'.img');
    }

    public function fetch(int $id, ?string $url): ?string
    {
        $path = $this->path($id);
        if (is_file($path)) return $path;
        $url = self::safeUrl($url);
        if (! $url || ! function_exists('curl_init')) return null;

        // Only connect to a validated public IPv4 address, pin DNS, forbid redirects
        // and proxies, and retain TLS certificate/hostname verification.
        $host = strtolower(parse_url($url, PHP_URL_HOST));
        $ips = @gethostbynamel($host) ?: [];
        $ip = null;
        foreach ($ips as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE)) {
                $ip = $candidate;
                break;
            }
        }
        if (! $ip) return null;
        $dir = dirname($path);
        if (! is_dir($dir) && ! @mkdir($dir, 0750, true) && ! is_dir($dir)) return null;
        // Bound disk use to 250 MiB. Existing cached files remain available.
        $lock = @fopen($dir.'/.lock', 'c');
        if (! $lock) return null;
        if (! flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); return null; }
        try {
            if (is_file($path)) return $path;
            $total = 0;
            foreach (glob($dir.'/*.img') ?: [] as $file) $total += filesize($file) ?: 0;
            $limit = min(2 * 1024 * 1024, 250 * 1024 * 1024 - $total);
            if ($limit <= 0) return null;
            $body = '';
            $ch = curl_init($url);
            if (! $ch) return null;
            curl_setopt_array($ch, [
                CURLOPT_RESOLVE => [$host.':443:'.$ip],
                CURLOPT_PROXY => '',
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT_MS => 1500,
                CURLOPT_TIMEOUT_MS => 3500,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'Mozilla/5.0',
                CURLOPT_WRITEFUNCTION => function ($handle, string $chunk) use (&$body, $limit) {
                    if (strlen($body) + strlen($chunk) > $limit) return 0;
                    $body .= $chunk;
                    return strlen($chunk);
                },
            ]);
            $ok = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            if ($ok === false || $status !== 200) return null;
            $info = @getimagesizefromstring($body);
            if (! $info || ! in_array($info['mime'] ?? '', ['image/jpeg','image/png','image/webp','image/gif'], true)
                || $info[0] * $info[1] > 25000000) return null;
            $tmp = tempnam($dir, 'photo-');
            if (! $tmp) return null;
            if (file_put_contents($tmp, $body) !== strlen($body) || ! @rename($tmp, $path)) {
                @unlink($tmp);
                return null;
            }
            return $path;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
