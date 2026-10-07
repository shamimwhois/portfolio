<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Support\AiConfig;
use App\Support\Payments as PaymentsSupport;
use App\Support\Social;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Integrations extends Component
{
    public string $search = '';

    public string $status = 'all'; // all | active | inactive

    public string $group = 'all'; // all | ai | payments | social | auth

    /**
     * Group definitions. Keys double as the group filter value.
     *
     * @return array<string, array{label: string, icon: string, color: string, description: string}>
     */
    #[Computed]
    public function groups(): array
    {
        return [
            'ai' => [
                'label' => 'AI Providers',
                'icon' => 'zap',
                'color' => '#7c3aed',
                'description' => 'Chat-completions providers that power the support assistant.',
            ],
            'payments' => [
                'label' => 'Payment Gateways',
                'icon' => 'cash',
                'color' => '#059669',
                'description' => 'Checkout providers and offline manual methods.',
            ],
            'social' => [
                'label' => 'Social Channels',
                'icon' => 'share',
                'color' => '#2563eb',
                'description' => 'Footer profile links, share buttons and the embedded feed.',
            ],
            'auth' => [
                'label' => 'Login & Security',
                'icon' => 'lock',
                'color' => '#dc2626',
                'description' => 'OAuth sign-in providers for the login and register pages.',
            ],
        ];
    }

    /**
     * The full plugin registry — every real integration the app can run,
     * derived live from settings so statuses never drift from the config
     * pages. Each plugin carries its group, brand styling, description,
     * status (active|inactive), a human-readable meta line, and the named
     * route its Manage button deep-links to.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function plugins(): array
    {
        // Keys must be unique across groups — a later spread silently
        // overwrites an earlier plugin on collision (e.g. the github social
        // channel vs GitHub OAuth). IntegrationsTest asserts the total count
        // to catch regressions.
        return [
            ...$this->aiPlugins(),
            ...$this->paymentPlugins(),
            ...$this->socialPlugins(),
            ...$this->authPlugins(),
        ];
    }

    private function aiPlugins(): array
    {
        $enabled = (bool) Setting::get('ai_enabled', true);
        $selected = AiConfig::provider();
        $config = AiConfig::all();
        $colors = [
            'openai' => '#10a37f',
            'deepseek' => '#4d6bfe',
            'openrouter' => '#6d28d9',
            'mistral' => '#f97316',
            'ollama' => '#4338ca',
            'lmstudio' => '#0ea5e9',
            'custom' => '#64748b',
        ];

        $plugins = [];

        foreach (config('llm.providers', []) as $key => $preset) {
            $label = (string) ($preset['label'] ?? ucfirst($key));
            $local = (bool) ($preset['local'] ?? false);
            $configured = $local || filled($config['api_key']);
            $selectedProvider = $key === $selected;
            $isActive = $enabled && $selectedProvider && $configured;

            $plugins[$key] = [
                'group' => 'ai',
                'label' => $label,
                'icon' => 'zap',
                'color' => $colors[$key] ?? '#6366f1',
                'description' => $local
                    ? 'Self-hosted models through your local '.$label.' server.'
                    : 'Hosted '.$label.' chat models via Chat Completions.',
                'status' => $isActive ? 'active' : 'inactive',
                'meta' => match (true) {
                    $isActive => 'Active provider · '.($config['model'] ?: 'model set'),
                    $selectedProvider && ! $configured => 'Selected · add an API key',
                    $selectedProvider && ! $enabled => 'Selected · assistant disabled',
                    $configured => 'Configured · switch provider',
                    default => 'Not configured',
                },
                'manage' => 'admin.settings.ai',
            ];
        }

        return $plugins;
    }

    private function paymentPlugins(): array
    {
        $payments = PaymentsSupport::all();
        $colors = [
            'stripe' => '#635bff',
            'bkash' => '#e2136e',
            'razorpay' => '#0a245b',
            'sslcommerz' => '#eb1d25',
            'amarpay' => '#6d28d9',
            'binance' => '#f0b90b',
        ];

        $plugins = [];

        foreach ($payments['gateways'] ?? [] as $key => $gateway) {
            $enabled = ! empty($gateway['enabled']);
            $ready = $this->gatewayReady($key, $gateway);
            $modes = $gateway['modes'] ?? ['test', 'live'];
            $mode = in_array($gateway['mode'] ?? null, $modes, true) ? $gateway['mode'] : $modes[0];

            $plugins[$key] = [
                'group' => 'payments',
                'label' => (string) ($gateway['label'] ?? ucfirst($key)),
                'icon' => 'cash',
                'color' => $colors[$key] ?? '#64748b',
                'description' => (string) ($gateway['type'] ?? 'Online payment gateway.'),
                'status' => $enabled && $ready ? 'active' : 'inactive',
                'meta' => match (true) {
                    $enabled && $ready => ucfirst($mode).' mode · ready for checkout',
                    $enabled => 'Enabled · credentials missing',
                    default => 'Disabled',
                },
                'manage' => 'admin.settings.payments',
            ];
        }

        $methods = $payments['methods'] ?? [];
        $enabledMethods = collect($methods)->filter(fn (array $method) => ! empty($method['enabled']))->count();

        $plugins['manual'] = [
            'group' => 'payments',
            'label' => 'Manual Methods',
            'icon' => 'cash',
            'color' => '#64748b',
            'description' => 'Offline fallback — bKash, Nagad, Binance P2P & PayPal.',
            'status' => $enabledMethods > 0 ? 'active' : 'inactive',
            'meta' => $enabledMethods > 0
                ? $enabledMethods.' of '.count($methods).' methods enabled'
                : 'All manual methods disabled',
            'manage' => 'admin.settings.payments',
        ];

        return $plugins;
    }

    /**
     * A gateway is ready when enabled and every required credential for the
     * active mode is filled in — env overrides (PAYMENTS_*) count too. Mirrors
     * the payments settings page so the registry agrees with checkout.
     */
    private function gatewayReady(string $key, array $gateway): bool
    {
        if (empty($gateway['enabled'])) {
            return false;
        }

        $modes = $gateway['modes'] ?? ['test', 'live'];
        $mode = in_array($gateway['mode'] ?? null, $modes, true) ? $gateway['mode'] : $modes[0];

        foreach ($gateway['fields'] ?? [] as $field) {
            if (! empty($field['optional'])) {
                continue;
            }

            $value = $gateway['credentials'][$mode][$field['key']] ?? null;
            $env = env('PAYMENTS_'.strtoupper($key).'_'.strtoupper($field['key']));

            if (! filled($value) && ! filled($env)) {
                return false;
            }
        }

        return true;
    }

    private function socialPlugins(): array
    {
        $plugins = [];

        foreach (Social::all() as $key => $channel) {
            $connected = $channel['enabled'] && filled($channel['url']);

            $plugins[$key] = [
                'group' => 'social',
                'label' => $channel['label'],
                'icon' => $channel['icon'],
                'color' => $channel['color'],
                'description' => 'Profile link rendered in the site footer.',
                'status' => $connected ? 'active' : 'inactive',
                'meta' => match (true) {
                    $connected => 'Connected · '.$channel['url'],
                    $channel['enabled'] => 'Enabled · add a URL',
                    default => 'Not configured',
                },
                'manage' => 'admin.settings.social',
            ];
        }

        $embed = Social::embed();
        $embedActive = ! empty($embed['enabled']) && filled($embed['html']);

        $plugins['embed'] = [
            'group' => 'social',
            'label' => 'Embedded Feed',
            'icon' => 'code',
            'color' => '#8b5cf6',
            'description' => 'Third-party feed snippet (Instagram, X timeline) on the homepage.',
            'status' => $embedActive ? 'active' : 'inactive',
            'meta' => match (true) {
                $embedActive => 'Live on the homepage',
                ! empty($embed['enabled']) => 'Enabled · add a snippet',
                default => 'Not configured',
            },
            'manage' => 'admin.settings.social',
        ];

        return $plugins;
    }

    private function authPlugins(): array
    {
        // Keys are registry identifiers — suffixed so they never collide with
        // the same-named social channel plugins (github is both a footer
        // channel and an OAuth provider).
        $definitions = [
            'google_oauth' => ['service' => 'google', 'label' => 'Google Sign-In', 'icon' => 'google', 'color' => '#4285f4'],
            'github_oauth' => ['service' => 'github', 'label' => 'GitHub Sign-In', 'icon' => 'github', 'color' => '#24292f'],
        ];

        $plugins = [];

        foreach ($definitions as $key => $definition) {
            $provider = config('services.'.$definition['service'], []);
            $configured = filled($provider['client_id'] ?? null) && filled($provider['client_secret'] ?? null);

            $plugins[$key] = [
                'group' => 'auth',
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'color' => $definition['color'],
                'description' => 'OAuth2 sign-in for the login and register pages.',
                'status' => $configured ? 'active' : 'inactive',
                'meta' => $configured
                    ? 'Client ID + secret set'
                    : 'Set '.strtoupper($definition['service']).'_CLIENT_ID / _CLIENT_SECRET env vars',
                'manage' => 'admin.settings.social',
            ];
        }

        return $plugins;
    }

    /**
     * Plugins narrowed by the active group tab, search and status filter.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function filteredPlugins(): array
    {
        return collect($this->plugins)
            ->filter(fn (array $plugin) => $this->matchesFilter($plugin))
            ->all();
    }

    private function matchesFilter(array $plugin): bool
    {
        if ($this->group !== 'all' && $plugin['group'] !== $this->group) {
            return false;
        }

        if ($this->search) {
            $haystack = strtolower(implode(' ', [
                $plugin['label'],
                $plugin['description'],
                $plugin['meta'],
                (string) $plugin['group'],
            ]));

            if (! str_contains($haystack, strtolower($this->search))) {
                return false;
            }
        }

        if ($this->status === 'active' && $plugin['status'] !== 'active') {
            return false;
        }

        if ($this->status === 'inactive' && $plugin['status'] === 'active') {
            return false;
        }

        return true;
    }

    /**
     * Registry summary for the workspace stat cards.
     *
     * @return array{total: int, groups: int, active: int, inactive: int}
     */
    #[Computed]
    public function metrics(): array
    {
        $active = collect($this->plugins)->where('status', 'active')->count();

        return [
            'total' => count($this->plugins),
            'groups' => count($this->groups),
            'active' => $active,
            'inactive' => count($this->plugins) - $active,
        ];
    }

    /**
     * Per-group plugin and active counts for the tabs and details bar.
     *
     * @return array<string, array{count: int, active: int}>
     */
    #[Computed]
    public function groupStats(): array
    {
        $stats = [];

        foreach (array_keys($this->groups) as $key) {
            $stats[$key] = ['count' => 0, 'active' => 0];
        }

        foreach ($this->plugins as $plugin) {
            if (! isset($stats[$plugin['group']])) {
                continue;
            }

            $stats[$plugin['group']]['count']++;

            if ($plugin['status'] === 'active') {
                $stats[$plugin['group']]['active']++;
            }
        }

        return $stats;
    }

    /**
     * The currently selected group (or the all-groups fallback) for the
     * details bar.
     *
     * @return array{label: string, icon: string, color: string, description: string}
     */
    #[Computed]
    public function activeGroup(): array
    {
        if ($this->group !== 'all' && isset($this->groups[$this->group])) {
            return $this->groups[$this->group];
        }

        return [
            'label' => 'All Integrations',
            'icon' => 'plug',
            'color' => '#6366f1',
            'description' => 'Every connected service across the platform.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.integrations');
    }
}
