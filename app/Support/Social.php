<?php

namespace App\Support;

use App\Models\Setting;

class Social
{
    /**
     * All configured channels, merged with config defaults and persisted
     * settings. A stored value may be either a plain URL string (legacy flat
     * keys) or an array with url/enabled. The flat legacy keys (facebook,
     * github, telegram, whatsapp) act as a fallback so pre-migration installs
     * keep working.
     *
     * @return array<string, array{label: string, icon: string, color: string, url: ?string, enabled: bool, placeholder: string}>
     */
    public static function all(): array
    {
        $stored = Setting::get('socials', []);
        $legacy = ['facebook', 'github', 'telegram', 'whatsapp'];
        $channels = [];

        foreach (config('socials.channels', []) as $key => $channel) {
            $value = $stored[$key] ?? null;

            if (is_string($value)) {
                $value = ['url' => $value, 'enabled' => filled($value)];
            }

            $value = is_array($value) ? $value : [];
            $url = $value['url'] ?? null;

            // Flat-key fallback (e.g. Setting::get('facebook')) for legacy installs.
            if (blank($url) && in_array($key, $legacy, true)) {
                $url = Setting::get($key);
            }

            $channels[$key] = [
                'label' => $channel['label'] ?? ucfirst($key),
                'icon' => $channel['icon'] ?? $key,
                'color' => $channel['color'] ?? '#6366f1',
                'placeholder' => $channel['placeholder'] ?? 'https://',
                'url' => filled($url) ? (string) $url : null,
                'enabled' => (bool) ($value['enabled'] ?? filled($url)),
            ];
        }

        return $channels;
    }

    /**
     * Channels that are enabled and have a URL — the ones rendered publicly.
     *
     * @return array<string, array{label: string, icon: string, color: string, url: string, enabled: bool, placeholder: string}>
     */
    public static function connected(): array
    {
        return array_filter(
            static::all(),
            fn (array $channel) => $channel['enabled'] && filled($channel['url'])
        );
    }

    /**
     * Count of channels currently connected (enabled + URL).
     */
    public static function connectedCount(): int
    {
        return count(static::connected());
    }

    /**
     * Share links for a page/post across the common networks.
     *
     * @return array<string, array{label: string, icon: string, color: string, url: string}>
     */
    public static function shareLinks(string $url, ?string $text = null): array
    {
        $text = $text ?: 'Check this out';
        $encodedUrl = urlencode($url);
        $encodedText = urlencode($text);

        return [
            'facebook' => [
                'label' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#1877f2',
                'url' => 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl,
            ],
            'x' => [
                'label' => 'Post on X',
                'icon' => 'x',
                'color' => '#111111',
                'url' => 'https://twitter.com/intent/tweet?url='.$encodedUrl.'&text='.$encodedText,
            ],
            'linkedin' => [
                'label' => 'LinkedIn',
                'icon' => 'linkedin',
                'color' => '#0a66c2',
                'url' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedUrl,
            ],
            'whatsapp' => [
                'label' => 'WhatsApp',
                'icon' => 'whatsapp',
                'color' => '#25d366',
                'url' => 'https://wa.me/?text='.$encodedText.'%20'.$encodedUrl,
            ],
            'telegram' => [
                'label' => 'Telegram',
                'icon' => 'telegram',
                'color' => '#229ed9',
                'url' => 'https://t.me/share/url?url='.$encodedUrl.'&text='.$encodedText,
            ],
        ];
    }

    /**
     * The optional embedded feed configuration.
     *
     * @return array{enabled: bool, title: string, html: string}
     */
    public static function embed(): array
    {
        $defaults = config('socials.embed', []);
        $stored = Setting::get('socials_embed', []);

        return array_replace($defaults, is_array($stored) ? $stored : []);
    }
}
