<?php

namespace App\Support;

use App\Models\Setting;

class Payments
{
    /**
     * Full payments config (currency + manual methods + gateways), stored in
     * settings with config/payments.php as the fallback seed. Settings are
     * merged over the config so removed keys never resurface.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $stored = Setting::get('payments', []);

        // Drop the pre-refactor single-gateway key so stale settings never
        // resurface alongside the per-gateway map.
        unset($stored['gateway']);

        return array_replace_recursive(config('payments'), $stored);
    }

    public static function currency(): string
    {
        return (string) (static::all()['currency'] ?? config('payments.currency'));
    }

    /**
     * Manual + gateway checkout methods. A gateway is exposed as a method only
     * when it is enabled and fully configured for the active mode.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function methods(): array
    {
        $methods = static::all()['methods'] ?? [];

        foreach (static::all()['gateways'] ?? [] as $key => $gateway) {
            if (static::isGatewayAvailable($key)) {
                $methods[$key] = [
                    'label' => $gateway['label'] ?? ucfirst($key),
                    'type' => $gateway['type'] ?? 'Online Payment',
                    'enabled' => true,
                    'instructions' => $gateway['instructions'] ?? 'You will be redirected to the payment gateway to complete your purchase.',
                    'gateway' => true,
                ];
            }
        }

        return $methods;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function method(string $key): ?array
    {
        return static::methods()[$key] ?? null;
    }

    public static function isEnabled(string $key): bool
    {
        return (bool) (static::method($key)['enabled'] ?? false);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function enabledMethods(): array
    {
        return array_filter(
            static::methods(),
            fn (array $method) => ($method['enabled'] ?? true)
        );
    }

    public static function isGateway(string $key): bool
    {
        return (bool) (static::method($key)['gateway'] ?? false);
    }

    /**
     * Gateway config for the given key, merged with its config defaults.
     *
     * @return array<string, mixed>
     */
    public static function gateway(string $key): array
    {
        $defaults = config('payments.gateways.'.$key, []);
        $stored = static::all()['gateways'][$key] ?? [];

        return array_replace_recursive($defaults, $stored);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function gateways(): array
    {
        return static::all()['gateways'] ?? config('payments.gateways', []);
    }

    public static function gatewayMode(string $key): string
    {
        return (string) (static::gateway($key)['mode'] ?? 'test');
    }

    /**
     * A gateway is available when enabled and every credential field for the
     * active mode has a value (env overrides count too).
     */
    public static function isGatewayAvailable(string $key): bool
    {
        $gateway = static::gateway($key);

        if (! ($gateway['enabled'] ?? false)) {
            return false;
        }

        foreach ($gateway['fields'] ?? [] as $field) {
            if (! empty($field['optional'])) {
                continue;
            }

            if (! filled(static::credential($key, $field['key']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve a credential: env override first, then the stored settings for
     * the gateway's active mode. Keeps secrets out of the database when the
     * operator prefers env vars (PAYMENTS_STRIPE_SECRET_KEY, ...).
     */
    public static function credential(string $key, string $field): ?string
    {
        $env = env('PAYMENTS_'.strtoupper($key).'_'.strtoupper($field));

        if (filled($env)) {
            return $env;
        }

        $mode = static::gatewayMode($key);

        $value = static::gateway($key)['credentials'][$mode][$field] ?? null;

        return filled($value) ? (string) $value : null;
    }
}
