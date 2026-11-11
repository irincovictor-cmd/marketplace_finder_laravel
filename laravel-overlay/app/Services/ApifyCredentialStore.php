<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Single-user, localhost-only Apify credential vault. Encryption uses the
 * existing persistent Laravel APP_KEY and never exposes the secret to JS.
 */
class ApifyCredentialStore
{
    private const PATH = 'private/marketplace/apify-token.enc';

    public function hasStoredToken(): bool
    {
        return Storage::disk('local')->exists(self::PATH);
    }

    public function getToken(): string
    {
        if ($this->hasStoredToken()) {
            // Fail closed for a corrupted vault; never send an invalid token.
            try {
                return Crypt::decryptString(Storage::disk('local')->get(self::PATH));
            } catch (DecryptException $e) {
                throw new \RuntimeException('Saved API key could not be decrypted. Replace it in settings.', 0, $e);
            }
        }
        return (string) config('marketplace.apify_token', '');
    }

    public function isConfigured(): bool
    {
        return $this->hasStoredToken() || trim((string) config('marketplace.apify_token', '')) !== '';
    }

    public function source(): string
    {
        return $this->hasStoredToken() ? 'encrypted_file'
            : ($this->isConfigured() ? 'environment' : 'none');
    }

    public function save(string $token): void
    {
        if (! Storage::disk('local')->put(self::PATH, Crypt::encryptString($token))) {
            throw new \RuntimeException('Could not persist the encrypted API key.');
        }
    }

    public function remove(): void
    {
        Storage::disk('local')->delete(self::PATH);
    }
}
