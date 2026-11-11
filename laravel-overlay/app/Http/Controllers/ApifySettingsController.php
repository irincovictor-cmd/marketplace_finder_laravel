<?php

namespace App\Http\Controllers;

use App\Services\ApifyCredentialStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApifySettingsController extends Controller
{
    private function localOnly(Request $request): ?JsonResponse
    {
        // App is designed for localhost only, not an internet-hosted settings API.
        // Host checks also reduce accidental exposure if port forwarding changes.
        if (! app()->environment('local', 'testing')
            || ! in_array(strtolower($request->getHost()), ['localhost', '127.0.0.1', '::1'], true)) {
            return response()->json(['message' => 'API key settings are available only on localhost.'], 403);
        }
        return null;
    }

    public function status(Request $request, ApifyCredentialStore $store): JsonResponse
    {
        if ($denied = $this->localOnly($request)) return $denied;
        return response()->json([
            'configured' => $store->isConfigured(),
            'source' => $store->source(),
            'can_remove' => $store->hasStoredToken(),
            'scan_enabled' => (bool) config('marketplace.scan_enabled') || $store->hasStoredToken(),
            'note' => 'The API key is never returned by this endpoint.',
        ])->header('Cache-Control', 'no-store');
    }

    public function save(Request $request, ApifyCredentialStore $store): JsonResponse
    {
        if ($denied = $this->localOnly($request)) return $denied;
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512', 'regex:/^apify_api_[A-Za-z0-9_-]+$/'],
        ]);
        $store->save($data['token']);

        return response()->json([
            'configured' => true,
            'source' => 'encrypted_file',
            'scan_enabled' => true,
            'message' => 'API key securely saved on this local Laravel server.',
        ])->header('Cache-Control', 'no-store');
    }

    public function remove(Request $request, ApifyCredentialStore $store): JsonResponse
    {
        if ($denied = $this->localOnly($request)) return $denied;
        $store->remove();

        return response()->json([
            'configured' => $store->isConfigured(),
            'source' => $store->source(),
            'scan_enabled' => (bool) config('marketplace.scan_enabled'),
            'message' => $store->isConfigured()
                ? 'Browser-saved key removed. An APIFY_TOKEN in your local .env is still configured.'
                : 'Saved key removed.',
        ])->header('Cache-Control', 'no-store');
    }
}
