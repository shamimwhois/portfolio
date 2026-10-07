<article class="rounded-2xl border border-white/50 bg-white/40 p-5 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="badge badge-brand">{{ $package->icon() }}</span>
            <div>
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ $package->title }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $package->kindLabel() }}
                    @if ($package->isSellable()) · <span class="font-semibold text-slate-900 dark:text-white">{{ $package->displayPrice() }}</span> @endif
                </p>
            </div>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2">
            <select
                wire:change="setStatus({{ $package->id }}, $event.target.value)"
                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $package->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($package->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                aria-label="Item visibility"
                name="publish_status"
            >
                @foreach (\App\Models\GroupPackage::STATUSES as $status)
                    <option value="{{ $status }}" @selected($package->publish_status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <x-admin.actions :view="route('servicegroup')" :edit="$package->id" :delete="$package->id" delete-message="Delete this item?" />
            </div>
        </div>
    </div>
    @if ($package->description)
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ $package->description }}</p>
    @endif
    @if (! empty($package->features))
        <ul class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($package->features as $feature)
                <li class="badge badge-neutral">{{ $feature }}</li>
            @endforeach
        </ul>
    @endif
</article>
