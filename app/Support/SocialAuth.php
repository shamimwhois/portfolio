<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAuth
{
    /**
     * Providers that are configured (client id + secret present).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function available(): array
    {
        return collect(['google', 'github'])
            ->filter(fn (string $provider) => static::configured($provider))
            ->mapWithKeys(fn (string $provider) => [$provider => static::config($provider)])
            ->all();
    }

    public static function configured(string $provider): bool
    {
        $config = static::config($provider);

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public static function config(string $provider): array
    {
        return config('services.'.$provider, []);
    }

    /**
     * Provider display name.
     */
    public static function label(string $provider): string
    {
        return ucfirst($provider);
    }

    /**
     * The redirect (callback) URI for a provider.
     */
    public static function redirectUri(string $provider): string
    {
        $configured = static::config($provider)['redirect'] ?? null;

        return $configured ?: route('auth.callback', $provider);
    }

    /**
     * Build the provider authorization URL with a CSRF-protecting state token.
     */
    public static function authorizeUrl(string $provider): string
    {
        $config = static::config($provider);

        $state = Str::random(40);

        session()->put('social_auth_state', $state);
        session()->put('social_auth_provider', $provider);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => static::redirectUri($provider),
            'response_type' => 'code',
            'scope' => implode(' ', $config['scopes'] ?? ['openid', 'email', 'profile']),
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return $config['authorize_url'].'?'.$query;
    }

    /**
     * Exchange the authorization code for a user profile via the token +
     * userinfo endpoints. Returns null on any provider or network error.
     *
     * @return array{id: string, name: string, email: string, avatar: ?string}|null
     */
    public static function handleCallback(string $provider, string $code): ?array
    {
        $config = static::config($provider);

        try {
            $tokenResponse = Http::asForm()->post($config['token_url'], [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => static::redirectUri($provider),
            ]);

            if (! $tokenResponse->successful() || blank($tokenResponse->json('access_token'))) {
                return null;
            }

            $accessToken = $tokenResponse->json('access_token');

            $headers = [
                'Authorization' => 'Bearer '.$accessToken,
                'Accept' => 'application/json',
            ];

            // GitHub requires a User-Agent header and prefers JSON media type.
            if ($provider === 'github') {
                $headers['X-GitHub-Api-Version'] = '2022-11-28';
            }

            $userResponse = Http::withHeaders($headers)->get($config['userinfo_url']);

            if (! $userResponse->successful()) {
                return null;
            }

            $user = $userResponse->json();
            $email = $user['email'] ?? null;

            // GitHub hides the primary email unless 'user:email' scope is used;
            // some setups still return it in the email list endpoint instead.
            if ($provider === 'github' && blank($email)) {
                $emails = Http::withHeaders($headers)->get('https://api.github.com/user/emails')->json();

                foreach ((array) $emails as $entry) {
                    if (($entry['primary'] ?? false) && ($entry['verified'] ?? false)) {
                        $email = $entry['email'] ?? null;
                        break;
                    }
                }
            }

            if (blank($email)) {
                return null;
            }

            return [
                'id' => (string) ($user['sub'] ?? $user['id'] ?? $provider.'-'.$email),
                'name' => (string) ($user['name'] ?? $user['login'] ?? 'New member'),
                'email' => (string) $email,
                'avatar' => $user['picture'] ?? $user['avatar_url'] ?? null,
            ];
        } catch (ConnectionException) {
            return null;
        }
    }
}
