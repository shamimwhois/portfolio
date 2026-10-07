<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Payments\GatewayManager;
use App\Payments\PaymentException;
use App\Support\Payments as PaymentsSupport;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Payments extends Component
{
    public array $payments = [];

    public string $search = '';

    public string $status = 'all'; // all | enabled | disabled

    public string $capability = 'all'; // all | ready | missing

    public ?string $editingKey = null;

    public string $editingMode = 'current'; // diagnostics segment: current | live | sandbox

    public ?array $diagnostics = null;

    /** @var array{success: bool, message: string, mode: string}|null */
    public ?array $connectionResult = null;

    public function mount(): void
    {
        $this->payments = array_replace_recursive(config('payments'), PaymentsSupport::all());
    }

    public function configure(string $key): void
    {
        if (isset($this->payments['gateways'][$key])) {
            $this->editingKey = $key;
            $this->editingMode = 'current';
            $this->diagnostics = null;
            $this->connectionResult = null;
        }
    }

    public function closeModal(): void
    {
        $this->editingKey = null;
        $this->editingMode = 'current';
        $this->diagnostics = null;
        $this->connectionResult = null;
    }

    public function setGatewayMode(string $key, string $mode): void
    {
        $modes = $this->payments['gateways'][$key]['modes'] ?? ['test', 'live'];

        if (in_array($mode, $modes, true)) {
            $this->payments['gateways'][$key]['mode'] = $mode;
        }
    }

    /**
     * Sandbox Mode toggle — ON means any non-live mode, OFF means live.
     */
    public function toggleSandboxMode(string $key): void
    {
        $gateway = $this->payments['gateways'][$key] ?? null;

        if (! $gateway) {
            return;
        }

        $modes = $gateway['modes'] ?? ['test', 'live'];
        $current = $gateway['mode'] ?? $modes[0];

        if ($current === 'live') {
            $target = collect($modes)->first(fn (string $mode) => $mode !== 'live') ?? $modes[0];
        } else {
            $target = 'live';
        }

        if (in_array($target, $modes, true)) {
            $this->payments['gateways'][$key]['mode'] = $target;
        }
    }

    public function toggleAvailability(string $key): void
    {
        $this->payments['gateways'][$key]['enabled'] = ! (bool) ($this->payments['gateways'][$key]['enabled'] ?? false);
    }

    /**
     * Validate the credentials stored for the selected mode (env overrides
     * count too) and report which required fields are missing, if any.
     */
    public function runDiagnostics(string $key): void
    {
        $gateway = $this->payments['gateways'][$key] ?? null;

        if (! $gateway) {
            return;
        }

        $modes = $gateway['modes'] ?? ['test', 'live'];
        $mode = $this->editingMode === 'current'
            ? (in_array($gateway['mode'] ?? null, $modes, true) ? $gateway['mode'] : $modes[0])
            : $this->editingMode;

        $missing = [];

        foreach ($gateway['fields'] ?? [] as $field) {
            if (! empty($field['optional'])) {
                continue;
            }

            $value = $gateway['credentials'][$mode][$field['key']] ?? null;
            $env = env('PAYMENTS_'.strtoupper($key).'_'.strtoupper($field['key']));

            if (! filled($value) && ! filled($env)) {
                $missing[] = $field['label'];
            }
        }

        $this->diagnostics = [
            'mode' => $mode,
            'ok' => empty($missing),
            'missing' => $missing,
        ];
    }

    /**
     * Ping the real provider API with the form-level (unsaved) credentials.
     * Temporarily persists the gateway config so the driver can resolve
     * credentials through Payments::credential(), then restores the original
     * stored settings. This way admins can test before saving.
     */
    public function testConnection(string $key): void
    {
        $this->connectionResult = null;

        $gateway = $this->payments['gateways'][$key] ?? null;

        if (! $gateway || empty($gateway['driver'])) {
            $this->connectionResult = [
                'success' => false,
                'message' => 'No gateway driver is registered for this key.',
            ];

            return;
        }

        $backup = Setting::get('payments', []);

        try {
            // Temporarily apply the form-level config so the driver can
            // read credentials through Payments::credential().
            Setting::set('payments', array_replace_recursive($backup, [
                'gateways' => [$key => $gateway],
            ]));

            $driver = app(GatewayManager::class)->driver($key);
            $mode = $driver->mode();
            $result = $driver->testConnection();
            $result['mode'] = $mode;
            $this->connectionResult = $result;
        } catch (PaymentException $e) {
            $this->connectionResult = [
                'success' => false,
                'message' => $e->getMessage(),
                'mode' => 'unknown',
            ];
        } catch (\Throwable $e) {
            $this->connectionResult = [
                'success' => false,
                'message' => 'Unexpected error: '.$e->getMessage(),
                'mode' => 'unknown',
            ];
        } finally {
            Setting::set('payments', $backup);
        }
    }

    public function save(): void
    {
        $this->validate([
            'payments.currency' => ['required', 'string', 'max:8'],
            'payments.gateways.*.enabled' => ['boolean'],
            'payments.gateways.*.mode' => ['required', 'in:test,live,sandbox'],
            'payments.gateways.*.credentials.*.*' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($this->payments['gateways'] ?? [] as $key => $gateway) {
            $modes = $this->payments['gateways'][$key]['modes'] ?? [];

            if (! in_array($gateway['mode'] ?? null, $modes, true)) {
                $this->payments['gateways'][$key]['mode'] = $modes[0] ?? 'test';
            }
        }

        foreach (array_keys($this->payments['methods'] ?? []) as $key) {
            $this->payments['methods'][$key]['enabled'] = (bool) ($this->payments['methods'][$key]['enabled'] ?? false);
        }

        Setting::set('payments', $this->payments);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Payment settings saved.');
        $this->closeModal();
    }

    /**
     * Readiness derived from the live form state so badges and metrics update
     * as the admin types, before anything is saved to settings.
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

    #[Computed]
    public function filteredGateways(): array
    {
        return collect($this->payments['gateways'] ?? [])
            ->filter(fn (array $gateway, string $key) => $this->matchesFilter($gateway, $key))
            ->all();
    }

    /**
     * Per-gateway readiness derived from the live form state (env overrides
     * count too). Single source of truth used by cards, filters and metrics
     * so badges never drift from the directory classification.
     *
     * @return array<string, bool>
     */
    #[Computed]
    public function readiness(): array
    {
        $map = [];

        foreach ($this->payments['gateways'] ?? [] as $key => $gateway) {
            $map[$key] = $this->gatewayReady($key, $gateway);
        }

        return $map;
    }

    /**
     * Directory summary: enabled, ready-for-checkout, enabled-but-missing
     * credentials, and enabled manual methods.
     */
    #[Computed]
    public function metrics(): array
    {
        $active = 0;
        $ready = 0;
        $missing = 0;

        foreach ($this->readiness as $key => $isReady) {
            if (empty($this->payments['gateways'][$key]['enabled'])) {
                continue;
            }

            $active++;

            if ($isReady) {
                $ready++;
            } else {
                $missing++;
            }
        }

        $manual = collect($this->payments['methods'] ?? [])->filter(
            fn (array $method) => ! empty($method['enabled'])
        )->count();

        return [
            'active' => $active,
            'ready' => $ready,
            'missing' => $missing,
            'manual' => $manual,
        ];
    }

    private function matchesFilter(array $gateway, string $key): bool
    {
        if ($this->search) {
            $haystack = strtolower(($gateway['label'] ?? $key).' '.$key);

            if (! str_contains($haystack, strtolower($this->search))) {
                return false;
            }
        }

        if ($this->status === 'enabled' && empty($gateway['enabled'])) {
            return false;
        }

        if ($this->status === 'disabled' && ! empty($gateway['enabled'])) {
            return false;
        }

        $ready = $this->readiness[$key] ?? false;

        if ($this->capability === 'ready' && ! $ready) {
            return false;
        }

        if ($this->capability === 'missing' && $ready) {
            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.admin.settings.payments', [
            'availableGateways' => PaymentsSupport::gateways(),
        ]);
    }
}
