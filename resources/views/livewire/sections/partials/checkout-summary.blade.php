@php
    $title = $kind === 'plan' ? $item->name : $item->title;
    $price = $kind === 'plan' ? $item->price : $item->displayPrice();
    $features = $item->features ?? [];
@endphp
<div class="rounded-2xl border border-white/60 bg-white/40 p-5 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Order summary</p>
            <h4 class="mt-1 font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h4>
            @if ($kind === 'package' && ($item->version || $item->type))
                <p class="mt-1.5 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                    @if ($item->version) <span class="badge badge-neutral">Version {{ $item->version }}</span> @endif
                    @if ($item->type) <span class="badge badge-neutral">{{ $item->type }}</span> @endif
                </p>
            @endif
        </div>
        <p class="text-right">
            <span class="font-display text-2xl font-bold text-slate-900 dark:text-white">{{ $price }}</span>
            <span class="block text-xs font-normal text-slate-500 dark:text-slate-400">{{ $item->billingLabel() }}</span>
        </p>
    </div>

    @if (! empty($features))
        <ul class="mt-4 space-y-2 border-t border-slate-200/60 pt-4 dark:border-white/10">
            @foreach ($features as $feature)
                <li class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                    <x-icon name="check" class="icon mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" />
                    <span>{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-4 flex items-center justify-between border-t border-slate-200/60 pt-4 dark:border-white/10">
        <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Total to pay</span>
        <span class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ $price }}</span>
    </div>
</div>
