<?php

namespace App\Services\Videc;

use App\Models\PortalUser;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PortalSessionService
{
    public function authenticate(string $email, string $loginToken): PortalUser
    {
        $normalizedEmail = Str::lower(trim($email));

        $portalUser = PortalUser::query()
            ->where('email', $normalizedEmail)
            ->first();

        if (!$portalUser) {
            throw ValidationException::withMessages([
                'email' => 'Portal user not found.',
            ]);
        }

        $storedTokenHash = (string) data_get($portalUser->metadata, 'last_login_token_hash', '');
        $incomingTokenHash = hash('sha256', $loginToken);

        if ($storedTokenHash === '' || !hash_equals($storedTokenHash, $incomingTokenHash)) {
            throw ValidationException::withMessages([
                'login_token' => 'Portal login token is invalid.',
            ]);
        }

        $createdAtIso = data_get($portalUser->metadata, 'last_login_token_created_at');
        $ttlMinutes = max(5, (int) config('api_security.portal_login_token_ttl_minutes', 1440));

        if ($createdAtIso) {
            $createdAt = Carbon::parse($createdAtIso);

            if ($createdAt->addMinutes($ttlMinutes)->isPast()) {
                throw ValidationException::withMessages([
                    'login_token' => 'Portal login token has expired.',
                ]);
            }
        }

        return $portalUser;
    }
}
