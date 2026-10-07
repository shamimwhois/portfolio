@props([
    'edit' => null,
    'delete' => null,
    'deleteMessage' => 'Delete this item?',
    'view' => null,
    'viewMethod' => null,
    'viewId' => null,
    'align' => 'right',
    'editMethod' => 'edit',
    'deleteMethod' => 'delete',
])

<div class="flex shrink-0 items-center gap-2 {{ $align === 'right' ? 'justify-end' : '' }}">
    @if ($viewMethod)
        <button type="button" class="btn btn-secondary btn-sm" wire:click="{{ $viewMethod }}({{ $viewId ?? $edit ?? $delete }})" aria-label="View details">
            <x-icon name="eye" class="icon h-3.5 w-3.5" /> View
        </button>
    @elseif ($view)
        <a href="{{ $view }}" class="btn btn-secondary btn-sm" target="_blank">
            <x-icon name="eye" class="icon h-3.5 w-3.5" /> View
        </a>
    @endif
    @if ($edit !== null)
        <button type="button" class="btn btn-secondary btn-sm" wire:click="{{ $editMethod }}({{ $edit }})">
            <x-icon name="edit" class="icon h-3.5 w-3.5" /> Edit
        </button>
    @endif
    @if ($delete !== null)
        <button type="button" class="btn btn-danger btn-sm" wire:click="{{ $deleteMethod }}({{ $delete }})" wire:confirm="{{ $deleteMessage }}">
            <x-icon name="trash" class="icon h-3.5 w-3.5" /> Delete
        </button>
    @endif
</div>
