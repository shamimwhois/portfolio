@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'hint' => null,
    'rows' => null,
    'icon' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">
            {{ $label }} @if ($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    @if ($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="{{ $rows ?? 3 }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'input resize-none']) }}
        >{{ $slot }}</textarea>
    @elseif ($type === 'select')
        <div class="relative">
            @if ($icon)
                <x-icon :name="$icon" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            @endif
            <select
                id="{{ $name }}"
                name="{{ $name }}"
                {{ $attributes->merge(['class' => 'input appearance-none pr-10' . ($icon ? ' pl-10' : '')]) }}
            >
                {{ $slot }}
            </select>
            <x-icon name="chevron-down" class="icon pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>
    @else
        <div class="relative">
            @if ($icon)
                <x-icon :name="$icon" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            @endif
            <input
                id="{{ $name }}"
                name="{{ $name }}"
                type="{{ $type }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->merge(['class' => 'input' . ($icon ? ' pl-10' : '')]) }}
            />
        </div>
    @endif

    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="error-text mt-1.5 flex items-center gap-1.5">
            <x-icon name="close" class="icon h-3.5 w-3.5" /> {{ $error }}
        </p>
    @endif
</div>
