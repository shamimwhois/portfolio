<?php

namespace App\Support;

use App\Models\Setting;

class Theme
{
    /**
     * Per-theme landing palette defaults. Each palette has three slots that
     * re-tint the public site when the theme is active:
     *   primary   → buttons, links & key accents (--color-brand-500/600)
     *   secondary → secondary highlights & info (--color-brand-300/400)
     *   accent    → success & positive accents (--color-emerald-*)
     *
     * @return array<string, array{primary: string, secondary: string, accent: string}>
     */
    public static function defaults(): array
    {
        return [
            'system' => ['primary' => '#4663EE', 'secondary' => '#22BDFF', 'accent' => '#17A86A'],
            'light' => ['primary' => '#4663EE', 'secondary' => '#22BDFF', 'accent' => '#17A86A'],
            'dark' => ['primary' => '#4663EE', 'secondary' => '#22BDFF', 'accent' => '#17A86A'],
            'light_ice' => ['primary' => '#467EAB', 'secondary' => '#6399BF', 'accent' => '#17A86A'],
            'light_rose' => ['primary' => '#B93A6E', 'secondary' => '#E576A3', 'accent' => '#17A86A'],
            'dark_aurora' => ['primary' => '#14AE8D', 'secondary' => '#33C9A8', 'accent' => '#17A86A'],
            'dark_orchid' => ['primary' => '#8B4FFF', 'secondary' => '#A37BFF', 'accent' => '#17A86A'],
        ];
    }

    public static function fallback(): array
    {
        return ['primary' => '#4663EE', 'secondary' => '#22BDFF', 'accent' => '#17A86A'];
    }

    /**
     * Resolved palette for a theme — saved overrides win, defaults fill the
     * rest. Unknown keys are dropped so a stale setting can't inject junk.
     *
     * @return array{primary: string, secondary: string, accent: string}
     */
    public static function palette(string $theme): array
    {
        $defaults = static::defaults()[$theme] ?? static::fallback();
        $stored = Setting::get('theme_palettes', []);
        $saved = is_array($stored[$theme] ?? null) ? $stored[$theme] : [];

        return array_replace($defaults, array_intersect_key($saved, $defaults));
    }

    /**
     * Runtime style override for the public landing. Emits a
     * [data-theme='…'] block for every theme with a customized palette, plus
     * always for the active default theme, re-mapping the brand/emerald token
     * shades so the whole Tailwind surface (buttons, links, badges) re-tints.
     * Dashboards and admin surfaces are untouched because the block lives in
     * the public hud layout only.
     */
    public static function styleTag(?string $default = null): string
    {
        $defaults = static::defaults();
        $default = $default ?? (string) Setting::get('theme_default', 'system');
        $blocks = [];

        foreach ($defaults as $theme => $defaultPalette) {
            if ($theme !== $default && static::palette($theme) === $defaultPalette) {
                continue;
            }

            $p = static::palette($theme);

            $blocks[] = sprintf(
                '[data-theme="%s"]{--color-brand-500:%s;--color-brand-600:%s;--color-brand-400:%s;--color-brand-300:%s;--color-emerald-400:%s;--color-emerald-500:%s;--color-emerald-600:%s}',
                $theme,
                $p['primary'],
                $p['primary'],
                $p['secondary'],
                $p['secondary'],
                $p['accent'],
                $p['accent'],
                $p['accent']
            );
        }

        return $blocks ? '<style>'.implode('', $blocks).'</style>' : '';
    }
}
