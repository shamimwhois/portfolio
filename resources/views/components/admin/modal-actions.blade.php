@props([
    'formId' => null,
    'saveLabel' => 'Save',
    'saveMethod' => 'save',
    'cancelMethod' => 'cancel',
    'cancelLabel' => 'Cancel',
])

<div class="flex flex-wrap items-center justify-end gap-2">
    <button type="button" class="btn btn-ghost btn-sm" wire:click="{{ $cancelMethod }}">
        {{ $cancelLabel }}
    </button>
    <button
        type="submit"
        @if ($formId) form="{{ $formId }}" @endif
        wire:loading.attr="disabled"
        class="btn btn-primary btn-sm"
    >
        <span wire:loading.remove wire:target="{{ $saveMethod }}" class="inline-flex items-center gap-2">
            <x-icon name="check" class="icon h-3.5 w-3.5" /> {{ $saveLabel }}
        </span>
        <span wire:loading wire:target="{{ $saveMethod }}" class="inline-flex items-center gap-2">
            <x-icon name="refresh" class="icon h-3.5 w-3.5 animate-spin" /> Saving...
        </span>
    </button>
</div>
