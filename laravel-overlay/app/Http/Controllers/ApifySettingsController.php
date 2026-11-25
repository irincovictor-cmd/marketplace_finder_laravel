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

    public function testConnection(Request $request, ApifyCredentialStore $store): JsonResponse
    {
        if ($denied = $this->localOnly($request)) return $denied;
        return $this->testerConnection($store);
    }

    // Public demo diagnostic: only fixed status messages, no token or account data.
    public function testerConnection(ApifyCredentialStore $store): JsonResponse
    {
        $reply = fn (string $status, string $message) => response()->json(['status' => $status, 'message' => $message])->header('Cache-Control', 'no-store');
        try { $token = $store->getToken(); }
        catch (\RuntimeException $e) { return $reply('storage_error', 'Saved key cannot be decrypted. Replace the key in Settings.'); }
        if (trim($token) === '') return $reply('missing', 'No API key configured. Save a key first.');
        try {
            $result = \Illuminate\Support\Facades\Http::withToken($token)->acceptJson()->timeout(15)->withOptions(['allow_redirects' => false])->get('https://api.apify.com/v2/users/me');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $reply('network_error', 'Cannot reach Apify from the server. Check DNS, internet and TLS connectivity.');
        }
        if ($result->successful() && is_array($result->json('data'))) return $reply('valid', 'API key accepted by Apify. This does not verify actor access, remaining credits or scan availability.');
        if ($result->status() === 401) return $reply('invalid', 'Apify rejected the API key (401). Replace it in Settings.');
        if ($result->status() === 403) return $reply('forbidden', 'Apify denied access (403). Check token permissions or account restrictions.');
        if ($result->status() === 429) return $reply('rate_limited', 'Apify rate limited the connection check (429).');
        return $reply('provider_error', 'Apify connection check returned HTTP '.$result->status().'.');
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
