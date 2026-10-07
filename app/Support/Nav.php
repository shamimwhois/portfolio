<?php

namespace App\Support;

class Nav
{
    /**
     * Filter config nav items by their role gate.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function visible(array $items): array
    {
        return array_values(array_filter(
            $items,
            fn (array $item) => static::visibleForRole($item['role'] ?? 'any')
                && (! static::isLogout($item) || auth()->check())
        ));
    }

    public static function isLogout(array $item): bool
    {
        return ($item['logout'] ?? false) === true;
    }

    public static function visibleForRole(string $role): bool
    {
        return match ($role) {
            'guest' => ! auth()->check(),
            'auth' => auth()->check(),
            'admin' => auth()->check() && auth()->user()->isAdmin(),
            'customer' => auth()->check() && ! auth()->user()->isAdmin(),
            default => true,
        };
    }

    /**
     * Build a URL for a nav item, appending its optional anchor.
     *
     * @param  array<string, mixed>  $item
     */
    public static function href(array $item): string
    {
        $href = route($item['route']);

        return isset($item['anchor']) ? $href.'#'.$item['anchor'] : $href;
    }
}
