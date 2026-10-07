<?php

namespace App\Support;

use App\Models\Setting;

class Seo
{
    public const ROBOTS = [
        'index,follow' => 'Index, follow',
        'index,nofollow' => 'Index, nofollow',
        'noindex,follow' => 'Noindex, follow',
        'noindex,nofollow' => 'Noindex, nofollow',
    ];

    /**
     * Built-in public pages that can carry their own SEO override. Keys are
     * route names; the layout resolves the current route against these.
     *
     * @return array<string, array{label: string, route: string, description: string}>
     */
    public static function pages(): array
    {
        return [
            'home' => ['label' => 'Home', 'route' => 'home', 'description' => 'Landing page'],
            'blog.index' => ['label' => 'Blog', 'route' => 'blog.index', 'description' => 'Blog index'],
            'blog.show' => ['label' => 'Blog post', 'route' => 'blog.show', 'description' => 'Single blog post'],
            'feed' => ['label' => 'Feed', 'route' => 'feed', 'description' => 'Community feed'],
            'contact' => ['label' => 'Contact', 'route' => 'contact', 'description' => 'Contact page'],
            'plan' => ['label' => 'Plans', 'route' => 'plan', 'description' => 'Subscription plans'],
            'servicelist' => ['label' => 'Service List', 'route' => 'servicelist', 'description' => 'Tool marketplace'],
            'servicegroup' => ['label' => 'Group Buy', 'route' => 'servicegroup', 'description' => 'Group-buy packages'],
        ];
    }

    /**
     * Global site-wide SEO defaults (the "Global SEO" primary record).
     *
     * @return array{meta_title: ?string, meta_description: ?string, robots: string, og_image: ?string, og_type: string, updated_at: ?string}
     */
    public static function global(): array
    {
        $stored = Setting::get('seo', []);

        return [
            'meta_title' => $stored['meta_title'] ?? null,
            'meta_description' => $stored['meta_description'] ?? null,
            'robots' => $stored['robots'] ?? 'index,follow',
            'og_image' => $stored['og_image'] ?? null,
            'og_type' => $stored['og_type'] ?? 'website',
            'updated_at' => $stored['updated_at'] ?? null,
        ];
    }

    /**
     * Per-page SEO overrides, keyed by route name (built-ins) or a generated
     * slug for custom paths. Each record: path?, label?, meta_title,
     * meta_description, robots, og_image, updated_at.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function records(): array
    {
        $stored = Setting::get('seo_pages', []);

        return is_array($stored) ? $stored : [];
    }

    /**
     * Custom records added via "Add SEO" — records without a known route.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function customRecords(): array
    {
        $builtins = array_keys(static::pages());

        return array_filter(
            static::records(),
            fn (string $key) => ! in_array($key, $builtins, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * All table rows: Global SEO first, then every built-in page, then any
     * custom records.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function rows(): array
    {
        $rows = ['global' => [...static::global(), 'kind' => 'global', 'label' => 'Global SEO', 'description' => 'Site-wide defaults', 'route' => null, 'path' => null]];

        foreach (static::pages() as $key => $page) {
            $record = static::records()[$key] ?? [];
            $rows[$key] = [
                'kind' => 'page',
                'label' => $page['label'],
                'description' => $page['description'],
                'route' => $page['route'],
                'path' => null,
                'meta_title' => $record['meta_title'] ?? null,
                'meta_description' => $record['meta_description'] ?? null,
                'robots' => $record['robots'] ?? null,
                'og_image' => $record['og_image'] ?? null,
                'updated_at' => $record['updated_at'] ?? null,
            ];
        }

        foreach (static::customRecords() as $key => $record) {
            $rows[$key] = [
                'kind' => 'custom',
                'label' => $record['path'] ?? $record['label'] ?? $key,
                'description' => 'Custom page',
                'route' => null,
                'path' => $record['path'] ?? null,
                'meta_title' => $record['meta_title'] ?? null,
                'meta_description' => $record['meta_description'] ?? null,
                'robots' => $record['robots'] ?? null,
                'og_image' => $record['og_image'] ?? null,
                'updated_at' => $record['updated_at'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Effective SEO for the current request: global defaults overridden by the
     * record matching the current route name (built-ins) or request path
     * (custom records). Consumers can rely on meta_title, meta_description,
     * robots, og_image and og_type always being present.
     *
     * @return array{meta_title: ?string, meta_description: ?string, robots: string, og_image: ?string, og_type: string}
     */
    public static function forRequest(?string $routeName = null, ?string $path = null): array
    {
        $routeName = $routeName ?? optional(request()->route())->getName();
        $path = ltrim($path ?? (string) request()->path(), '/');

        $seo = static::global();

        // Built-in page override by route name.
        if ($routeName && isset(static::pages()[$routeName])) {
            $seo = array_replace($seo, static::records()[$routeName] ?? []);
        }

        // Custom record override by path.
        foreach (static::customRecords() as $record) {
            if (ltrim((string) ($record['path'] ?? ''), '/') === $path) {
                $seo = array_replace($seo, $record);
                break;
            }
        }

        return [
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
            'robots' => $seo['robots'] ?? 'index,follow',
            'og_image' => $seo['og_image'] ?? null,
            'og_type' => $seo['og_type'] ?? 'website',
        ];
    }
}
