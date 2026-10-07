<div>
    <form wire:submit="save" class="space-y-6">
        <section class="card card-pad">
            <h2 class="card-title">AI Assistant</h2>
            <div class="mt-4 space-y-4">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="ai_enabled" class="h-4 w-4" /> Enable the Support AI widget
                </label>

                <div class="rounded-2xl border border-white/50 bg-white/40 p-4 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Provider</p>
                    <div class="mt-3 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="ai_provider" class="label">Provider</label>
                            <select id="ai_provider" wire:model.live="ai_provider" wire:change="providerChanged" class="input">
                                @foreach (\App\Support\AiConfig::providers() as $key => $preset)
                                    <option value="{{ $key }}">{{ $preset['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($ai_provider === 'custom')
                            <x-admin.field label="Provider name" name="ai_custom_name" wire:model="ai_custom_name" error="{{ $errors->first('ai_custom_name') }}" />
                        @endif
                        <x-admin.field label="Base URL" name="ai_config_base_url" wire:model="ai_config_base_url" error="{{ $errors->first('ai_config_base_url') }}" />
                        <x-admin.field label="API key" name="ai_config_api_key" type="password" wire:model="ai_config_api_key" error="{{ $errors->first('ai_config_api_key') }}" />
                        <x-admin.field label="Model" name="ai_config_model" wire:model="ai_config_model" error="{{ $errors->first('ai_config_model') }}" />
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button type="button" wire:click="testConnection" class="btn btn-secondary btn-sm">Test connection</button>
                        @if ($testResult === 'ok')
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Connected</span>
                        @elseif ($testResult !== null)
                            <span class="text-xs text-red-600 dark:text-red-400">{{ $testResult }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Credentials are stored in settings and fall back to USER_LLM_* env values when blank.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.field label="Assistant name" name="ai_name" wire:model="ai_name" />
                    <x-admin.field label="Welcome message" name="ai_welcome" wire:model="ai_welcome" />
                    <div class="md:col-span-2">
                        <x-admin.field label="System prompt / persona" name="ai_prompt" type="textarea" rows="3" wire:model="ai_prompt" />
                    </div>
                </div>
            </div>
        </section>

        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
    </form>
</div>
