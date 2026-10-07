<div>
    {{-- Header --}}
    <section class="card card-pad flex flex-wrap items-end justify-between gap-6">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">Payment Operations</p>
            <h2 class="card-title mt-1 text-2xl">Payment Gateways</h2>
            <p class="card-subtitle">
                Configure checkout providers, credentials, sandbox vs live modes, and availability from one control
                surface. Gateways appear at checkout only when enabled <em>and</em> every credential for the active
                mode is filled in — or overridden via <code class="font-mono text-[11px]">PAYMENTS_*</code> env vars.
            </p>
        </div>
        <div class="w-full sm:w-44">
            <x-admin.field label="Currency" name="payments.currency" required error="{{ $errors->first('payments.currency') }}" wire:model="payments.currency" />
        </div>
    </section>

    {{-- Metric cards --}}
    <section class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="card card-pad">
            <p class="stat-label">Active</p>
            <p class="stat-value mt-1 text-brand-600 dark:text-brand-400">{{ $this->metrics['active'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enabled gateways</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Ready</p>
            <p class="stat-value mt-1 text-emerald-600 dark:text-emerald-400">{{ $this->metrics['ready'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Ready for checkout</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Missing</p>
            <p class="stat-value mt-1 text-amber-600 dark:text-amber-400">{{ $this->metrics['missing'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enabled, needs credentials</p>
        </div>
        <div class="card card-pad">
            <p class="stat-label">Manual</p>
            <p class="stat-value mt-1 text-slate-900 dark:text-white">{{ $this->metrics['manual'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Offline methods enabled</p>
        </div>
    </section>

    {{-- Gateway directory --}}
    <section class="card card-pad mt-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="card-title">Gateway Directory</h3>
                <p class="card-subtitle">Review provider readiness before opening credential settings.</p>
            </div>
            <span class="badge badge-neutral">{{ count($this->filteredGateways) }} {{ count($this->filteredGateways) === 1 ? 'gateway' : 'gateways' }}</span>
        </div>

        {{-- Filter bar --}}
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:max-w-xs">
                <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="gateway_search" wire:model.live.debounce.250ms="search" class="input pl-10" placeholder="Name or code..." aria-label="Search gateways" />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <select wire:model.live="status" class="input !w-auto" aria-label="Status filter">
                    <option value="all">All statuses</option>
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
                <select wire:model.live="capability" class="input !w-auto" aria-label="Capability filter">
                    <option value="all">All capabilities</option>
                    <option value="ready">Ready for checkout</option>
                    <option value="missing">Missing credentials</option>
                </select>
            </div>
        </div>

        {{-- Gateway grid --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($this->filteredGateways as $key => $gateway)
                @php
                    $modes = $gateway['modes'] ?? ['test', 'live'];
                    $activeMode = in_array($gateway['mode'] ?? null, $modes, true) ? $gateway['mode'] : $modes[0];
                    $configured = $this->readiness[$key] ?? false;
                @endphp
                <article class="card card-pad card-hover flex flex-col !p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-bold text-white shadow-md shadow-brand-600/25">
                                {{ strtoupper(substr($gateway['label'] ?? $key, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-display text-sm font-semibold text-slate-900 dark:text-white">{{ $gateway['label'] ?? ucfirst($key) }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $key }}</p>
                            </div>
                        </div>
                        <span class="{{ ! empty($gateway['enabled']) ? 'badge badge-success' : 'badge badge-neutral' }} uppercase">
                            {{ ! empty($gateway['enabled']) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-1.5 text-xs">
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="check-circle" class="icon h-3.5 w-3.5 {{ $configured ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600' }}" />
                            {{ $configured ? 'Ready for checkout' : 'Missing credentials' }}
                        </p>
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="layers" class="icon h-3.5 w-3.5 text-brand-500" />
                            {{ ucfirst($activeMode) }} mode · {{ $gateway['type'] ?? 'Online Payment' }}
                        </p>
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                        <span class="badge badge-neutral">{{ count($gateway['fields'] ?? []) }} credential fields</span>
                        <button type="button" wire:click="configure('{{ $key }}')" class="btn btn-primary btn-sm">
                            <x-icon name="settings" class="icon h-3.5 w-3.5" /> Configure
                        </button>
                    </div>
                </article>
            @empty
                <p class="col-span-full py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                    No gateways match your filters.
                </p>
            @endforelse
        </div>
    </section>

    {{-- Manual methods --}}
    <section class="card card-pad mt-6">
        <h3 class="card-title">Manual methods</h3>
        <p class="card-subtitle">
            Offline fallback channels — customers pay into the account shown and you confirm the order manually.
            These are always available alongside any enabled gateways.
        </p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($payments['methods'] as $key => $method)
                @php
                    $methodDefaults = [
                        'label' => $method['label'] ?? ucfirst($key),
                        'type' => $method['type'] ?? 'Manual',
                        'account' => $method['account'] ?? '',
                    ];
                @endphp
                <article
                    class="card card-pad card-hover flex flex-col !p-4"
                    x-data="{ open: false, preview: {{ \Illuminate\Support\Js::from($methodDefaults) }} }"
                >
                    {{-- Card header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-bold text-white shadow-md shadow-brand-600/25">
                                <span x-text="(preview.label || '').trim().slice(0, 2).toUpperCase()">{{ strtoupper(substr($methodDefaults['label'], 0, 2)) }}</span>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-display text-sm font-semibold text-slate-900 dark:text-white" x-text="preview.label">{{ $methodDefaults['label'] }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="preview.type">{{ $methodDefaults['type'] }}</p>
                            </div>
                        </div>
                        <span class="{{ ! empty($method['enabled']) ? 'badge badge-success' : 'badge badge-neutral' }} uppercase">
                            {{ ! empty($method['enabled']) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    {{-- Summary --}}
                    <div class="mt-4 space-y-1.5 text-xs">
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="receipt" class="icon h-3.5 w-3.5 text-brand-500" />
                            <span class="truncate" x-text="preview.account || 'No account set'">{{ $method['account'] ?: 'No account set' }}</span>
                        </p>
                        <p class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <x-icon name="check-circle" class="icon h-3.5 w-3.5 {{ ! empty($method['enabled']) ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600' }}" />
                            {{ ! empty($method['enabled']) ? 'Visible at checkout' : 'Hidden at checkout' }}
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                        <span class="badge badge-neutral">3 fields</span>
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            @click="open = !open"
                            :aria-expanded="open ? 'true' : 'false'"
                            aria-controls="manual-{{ $key }}-fields"
                        >
                            <x-icon name="settings" class="icon h-3.5 w-3.5" />
                            <span x-text="open ? 'Close' : 'Configure'"></span>
                        </button>
                    </div>

                    {{-- Collapsible config panel --}}
                    <div
                        id="manual-{{ $key }}-fields"
                        x-show="open"
                        x-collapse
                        x-cloak
                        class="mt-4 border-t border-slate-200/50 pt-4 dark:border-slate-700/30"
                    >
                        <label class="mb-3 flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="payments.methods.{{ $key }}.enabled" class="h-4 w-4 rounded border-slate-400 text-brand-600 focus:ring-brand-500/30 dark:border-slate-600 dark:bg-slate-700" />
                            Enabled at checkout
                        </label>
                        <div class="grid gap-4 md:grid-cols-3">
                            <x-admin.field label="Label" name="payments.methods.{{ $key }}.label" x-model="preview.label" @input.debounce.400ms="$wire.set('payments.methods.{{ $key }}.label', preview.label)" @change="$wire.set('payments.methods.{{ $key }}.label', preview.label)" />
                            <x-admin.field label="Type" name="payments.methods.{{ $key }}.type" x-model="preview.type" @input.debounce.400ms="$wire.set('payments.methods.{{ $key }}.type', preview.type)" @change="$wire.set('payments.methods.{{ $key }}.type', preview.type)" />
                            <x-admin.field label="Account" name="payments.methods.{{ $key }}.account" x-model="preview.account" @input.debounce.400ms="$wire.set('payments.methods.{{ $key }}.account', preview.account)" @change="$wire.set('payments.methods.{{ $key }}.account', preview.account)" />
                            <div class="md:col-span-3">
                                <x-admin.field label="Instructions" name="payments.methods.{{ $key }}.instructions" type="textarea" rows="2" wire:model="payments.methods.{{ $key }}.instructions" />
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="mt-6 flex justify-end">
        <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
            <x-icon name="check" class="icon h-3.5 w-3.5" /> Save Settings
        </button>
    </div>

    {{-- Gateway Settings modal --}}
    @if ($editingKey && isset($payments['gateways'][$editingKey]))
        @php
            $gw = $payments['gateways'][$editingKey];
            $modes = $gw['modes'] ?? ['test', 'live'];
            $activeMode = in_array($gw['mode'] ?? null, $modes, true) ? $gw['mode'] : $modes[0];
            $configured = $this->readiness[$editingKey] ?? false;
            $webhookUrl = route('payment.webhook', ['gateway' => $editingKey]);
        @endphp
        <div
            class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="gateway-modal-title"
            data-close-method="closeModal"
            x-data="adminModal"
            @keydown.escape.window="close()"
        >
            <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-md" wire:click="closeModal" aria-hidden="true"></div>

            <div class="glass-strong animate-modal-in relative w-full overflow-hidden rounded-t-2xl sm:max-w-2xl sm:rounded-2xl">
                {{-- Modal header --}}
                <div class="flex items-center justify-between gap-4 border-b border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500/15 text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                            <x-icon name="settings" class="icon h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 id="gateway-modal-title" class="truncate font-display text-lg font-semibold text-slate-900 dark:text-white">Gateway Settings</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Credentials, webhooks, and connectivity</p>
                        </div>
                    </div>
                    <button type="button" class="icon-btn shrink-0" wire:click="closeModal" aria-label="Close gateway settings">
                        <x-icon name="close" class="icon h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-5">
                    {{-- Gateway banner --}}
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-bold text-white shadow-md shadow-brand-600/25">
                                {{ strtoupper(substr($gw['label'] ?? $editingKey, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-display text-base font-semibold text-slate-900 dark:text-white">{{ $gw['label'] ?? ucfirst($editingKey) }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $editingKey }}</p>
                            </div>
                        </div>
                        <span class="{{ $configured ? 'badge badge-success' : 'badge badge-warning' }} shrink-0">
                            <x-icon name="check" class="icon h-3 w-3" />
                            {{ $configured ? 'Ready for checkout' : 'Missing credentials' }}
                        </span>
                    </div>

                    {{-- Credential vault --}}
                    <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-center gap-2">
                            <x-icon name="lock" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                            <div>
                                <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">Credential vault</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Paste the credentials issued by the provider.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="label">Mode</label>
                                <select wire:model="payments.gateways.{{ $editingKey }}.mode" class="input" aria-label="Gateway mode">
                                    @foreach ($modes as $mode)
                                        <option value="{{ $mode }}">{{ ucfirst($mode) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end pb-1">
                                <span class="badge {{ ! empty($gw['enabled']) ? 'badge-success' : 'badge-neutral' }}">
                                    <x-icon name="dot" class="icon h-2.5 w-2.5" />
                                    {{ ! empty($gw['enabled']) ? 'API enabled' : 'API disabled' }}
                                </span>
                            </div>

                            @foreach ($gw['fields'] ?? [] as $field)
                                <x-admin.field
                                    :label="$field['label'].(empty($field['optional']) ? ' *' : ' (optional)')"
                                    :name="'payments.gateways.'.$editingKey.'.credentials.'.$activeMode.'.'.$field['key']"
                                    wire:model="payments.gateways.{{ $editingKey }}.credentials.{{ $activeMode }}.{{ $field['key'] }}"
                                    placeholder="{{ $activeMode === 'live' ? 'Live credential' : ucfirst($activeMode).' credential' }}"
                                    hint="{{ $field['key'] }} · {{ $activeMode }} mode"
                                />
                            @endforeach
                        </div>
                    </div>

                    {{-- Webhook + diagnostics --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center gap-2">
                                <x-icon name="link" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                <div>
                                    <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">Webhook endpoint</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Receive provider events here.</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2" x-data="{ copied: false }">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $webhookUrl }}"
                                    class="input !py-2 font-mono text-[11px]"
                                    aria-label="Webhook URL"
                                />
                                <button
                                    type="button"
                                    class="icon-btn shrink-0"
                                    @click="navigator.clipboard.writeText('{{ $webhookUrl }}'); copied = true; setTimeout(() => copied = false, 1600)"
                                    aria-label="Copy webhook URL"
                                >
                                    <x-icon name="copy" class="icon h-4 w-4" x-show="!copied" />
                                    <x-icon name="check" class="icon h-4 w-4 text-emerald-500" x-show="copied" x-cloak />
                                </button>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center gap-2">
                                <x-icon name="activity" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                <div>
                                    <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">Connection diagnostics</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Validate credentials for a mode.</p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <div class="flex rounded-xl border border-slate-200/80 p-0.5 dark:border-white/10" role="tablist" aria-label="Diagnostics mode">
                                    @foreach (array_merge(['current'], array_values($modes)) as $option)
                                        <button
                                            type="button"
                                            role="tab"
                                            wire:click="$set('editingMode', '{{ $option }}')"
                                            class="rounded-lg px-3 py-1 text-xs font-semibold transition-all duration-200 {{ $editingMode === $option ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-500/10 dark:text-slate-300' }}"
                                        >
                                            {{ ucfirst($option) }}
                                        </button>
                                    @endforeach
                                </div>
                                <button type="button" wire:click="runDiagnostics('{{ $editingKey }}')" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                                    <x-icon name="zap" class="icon h-3.5 w-3.5" /> Run diagnostics
                                </button>
                                <button type="button" wire:click="testConnection('{{ $editingKey }}')" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                    <x-icon name="activity" class="icon h-3.5 w-3.5" /> Test connection
                                </button>
                            </div>

                            @if ($diagnostics)
                                @if ($diagnostics['ok'])
                                    <p class="mt-3 flex items-center gap-2 rounded-xl bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                        <x-icon name="check-circle" class="icon h-4 w-4" />
                                        {{ ucfirst($diagnostics['mode']) }} mode — all required credentials present.
                                    </p>
                                @else
                                    <p class="mt-3 rounded-xl bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-700 dark:text-amber-300">
                                        <span class="flex items-center gap-2">
                                            <x-icon name="close" class="icon h-4 w-4" />
                                            {{ ucfirst($diagnostics['mode']) }} mode — missing: {{ implode(', ', $diagnostics['missing']) }}
                                        </span>
                                    </p>
                                @endif
                            @endif

                            @if ($connectionResult)
                                @php $cr = $connectionResult; @endphp
                                <p class="mt-3 flex items-start gap-2 rounded-xl px-3 py-2 text-xs font-medium {{ $cr['success'] ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-red-500/10 text-red-700 dark:text-red-300' }} break-all">
                                    <x-icon name="{{ $cr['success'] ? 'check-circle' : 'alert-circle' }}" class="icon mt-0.5 h-4 w-4 shrink-0" />
                                    <span>
                                        <span class="font-semibold">{{ ucfirst($cr['mode'] ?? 'current') }} mode: </span>
                                        {{ $cr['message'] }}
                                    </span>
                                </p>
                            @endif

                            <div class="mt-3" wire:loading wire:target="testConnection">
                                <p class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" opacity="0.3" />
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round" />
                                    </svg>
                                    Testing connection to the provider sandbox…
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Runtime settings --}}
                    <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                        <p class="font-display text-sm font-semibold text-slate-900 dark:text-white">Runtime settings</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Control how this integration behaves in production.</p>

                        <div class="mt-3 divide-y divide-slate-200/60 dark:divide-white/10">
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-icon name="server" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Sandbox Mode</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Provider test environment — no real charges.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        wire:click="toggleSandboxMode('{{ $editingKey }}')"
                                        @checked($activeMode !== 'live')
                                    />
                                    <span class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 peer-checked:bg-brand-600 peer-checked:after:translate-x-5 dark:bg-white/15"></span>
                                </label>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-icon name="zap" class="icon h-4 w-4 text-emerald-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">API availability</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Allow live production requests.</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        wire:click="toggleAvailability('{{ $editingKey }}')"
                                        @checked(! empty($gw['enabled']))
                                    />
                                    <span class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200 peer-checked:bg-brand-600 peer-checked:after:translate-x-5 dark:bg-white/15"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-200/60 px-5 py-4 dark:border-white/10">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary btn-sm">Cancel</button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                        <x-icon name="check-circle" class="icon h-3.5 w-3.5" /> Save changes
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
