<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\GroupPackage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class GroupPackages extends Component
{
    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $kind = 'tool';

    public string $title = '';

    public string $icon = '';

    public string $description = '';

    public string $features = '';

    public ?string $price = null;

    public string $billing = 'month';

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(GroupPackage $package): void
    {
        $this->resetForm();
        $this->editingId = $package->id;
        $this->showForm = true;
        $this->kind = $package->kind;
        $this->title = $package->title;
        $this->icon = $package->icon ?? '';
        $this->description = $package->description ?? '';
        $this->features = implode("\n", $package->features ?? []);
        $this->price = $package->price !== null ? rtrim(rtrim(number_format((float) $package->price, 2), '0'), '.') : null;
        $this->billing = $package->billing ?? 'month';
        $this->sort_order = $package->sort_order;
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'kind' => ['required', Rule::in(GroupPackage::KINDS)],
            'title' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:8'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'billing' => ['nullable', Rule::in(GroupPackage::BILLINGS)],
            'sort_order' => ['integer'],
        ]);

        $data = [
            'kind' => $validated['kind'],
            'title' => $validated['title'],
            'icon' => $validated['icon'] ?: null,
            'description' => $validated['description'] ?: null,
            'features' => collect(explode("\n", $validated['features']))->map(fn ($f) => trim($f))->filter()->values()->all(),
            'price' => $validated['price'] !== null && $validated['price'] !== '' ? (float) $validated['price'] : null,
            'billing' => $validated['billing'] ?: 'month',
            'sort_order' => $validated['sort_order'],
        ];

        if ($this->editingId) {
            $package = GroupPackage::query()->findOrFail($this->editingId);
            $package->update($data);
            session()->flash('status', 'Group item updated.');
        } else {
            $package = GroupPackage::query()->create($data);
            ActivityLog::record('admin.group_package.created', auth()->user(), ['package' => $package->title]);
            session()->flash('status', 'Group item created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(GroupPackage $package): void
    {
        ActivityLog::record('admin.group_package.deleted', auth()->user(), ['package' => $package->title]);
        $package->delete();
        session()->flash('status', 'Group item deleted.');
    }

    public function setStatus(GroupPackage $package, string $status): void
    {
        if (! in_array($status, GroupPackage::STATUSES, true)) {
            return;
        }

        $package->update(['publish_status' => $status]);
        ActivityLog::record('admin.group_package.status_changed', auth()->user(), ['package' => $package->title, 'status' => $status]);
        session()->flash('status', 'Item marked as '.ucfirst($status).'.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function combos()
    {
        return GroupPackage::query()
            ->where('kind', GroupPackage::KIND_COMBO)
            ->orderBy('sort_order')
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function tools()
    {
        return GroupPackage::query()
            ->where('kind', GroupPackage::KIND_TOOL)
            ->orderBy('sort_order')
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->combos->count() > $this->limit || $this->tools->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return GroupPackage::query()->count();
    }

    private function resetForm(): void
    {
        $this->reset('icon', 'title', 'description', 'features', 'price', 'sort_order');
        $this->kind = 'tool';
        $this->billing = 'month';
    }

    public function render()
    {
        return view('livewire.admin.group-packages');
    }
}
