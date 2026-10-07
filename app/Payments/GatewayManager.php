<?php

namespace App\Payments;

use App\Payments\Contracts\Gateway;
use App\Support\Payments;

class GatewayManager
{
    /** @var array<string, Gateway> */
    private array $drivers = [];

    /**
     * Resolve the driver registered for the given gateway key.
     */
    public function driver(string $key): Gateway
    {
        if (isset($this->drivers[$key])) {
            return $this->drivers[$key];
        }

        $config = Payments::gateway($key);

        if (empty($config['driver']) || ! class_exists($config['driver'])) {
            throw new PaymentException("No payment gateway driver registered for [{$key}].");
        }

        return $this->drivers[$key] = app($config['driver'], ['key' => $key]);
    }

    /**
     * Whether a driver is registered for the key (regardless of config).
     */
    public function has(string $key): bool
    {
        $config = Payments::gateway($key);

        return ! empty($config['driver']) && class_exists($config['driver']);
    }

    /**
     * Keys of gateways that are enabled AND fully configured, ready for
     * checkout. Single source of truth lives in Payments::methods() so the
     * checkout method list and this list can never drift.
     *
     * @return array<int, string>
     */
    public function available(): array
    {
        return array_keys(array_filter(
            Payments::methods(),
            fn (array $method) => ($method['gateway'] ?? false) === true,
        ));
    }
}
