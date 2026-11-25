<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;

class RemoteDemoAccess
{
    public static function proxied(Request $request): bool
    {
        return $request->header('X-Marketplace-Demo-Proxy') === '1';
    }

    public static function code(): string
    {
        return substr(hash_hmac('sha256', 'marketplace-remote-owner-v1', (string) config('app.key')), 0, 32);
    }

    public static function authorized(Request $request): bool
    {
        return ! self::proxied($request) || hash_equals(self::code(), (string) $request->session()->get('remote_owner_access', ''));
    }

    public function handle(Request $request, Closure $next)
    {
        if (! self::authorized($request)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Remote owner sign-in required.'], 403)
                : redirect('/demo-access');
        }
        return $next($request);
    }
}
