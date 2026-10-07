<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\TechPackage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Packages extends Component
{
    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $category = 'javascript';

    public string $icon = '';

    public string $title = '';

    public string $version = '';

    public string $type = '';

    public string $description = '';

    public ?string $price = null;

    public string $billing = 'one-time';

    public string $validity = '30 Days';

    public string $command = '';

    public string $features = '';

    public string $installCommands = '';

    public string $repoUrl = '';

    public string $docsUrl = '';

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(TechPackage $package): void
    {
        $this->resetForm();
        $this->editingId = $package->id;
        $this->showForm = true;
        $this->category = $package->category;
        $this->icon = $package->icon ?? '';
        $this->title = $package->title;
        $this->version = $package->version ?? '';
        $this->type = $package->type ?? '';
        $this->description = $package->description ?? '';
        $this->price = $package->price !== null ? rtrim(rtrim(number_format((float) $package->price, 2), '0'), '.') : null;
        $this->billing = $package->billing ?? 'one-time';
        $this->validity = $package->validity ?? '30 Days';
        $this->command = $package->command ?? '';
        $this->features = implode("\n", $package->features ?? []);
        $this->installCommands = implode("\n", $package->install_commands ?? []);
        $this->repoUrl = $package->repo_url ?? '';
        $this->docsUrl = $package->docs_url ?? '';
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
            'category' => ['required', Rule::in(TechPackage::CATEGORIES)],
            'icon' => ['nullable', 'string', 'max:8'],
            'title' => ['required', 'string', 'max:120'],
            'version' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'billing' => ['nullable', Rule::in(TechPackage::BILLINGS)],
            'validity' => ['nullable', 'string', 'max:40'],
            'command' => ['nullable', 'string', 'max:300'],
            'features' => ['nullable', 'string', 'max:2000'],
            'installCommands' => ['nullable', 'string', 'max:2000'],
            'repoUrl' => ['nullable', 'url', 'max:500'],
            'docsUrl' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['integer'],
        ]);

        $data = [
            'category' => $validated['category'],
            'icon' => $validated['icon'] ?: null,
            'title' => $validated['title'],
            'version' => $validated['version'] ?: null,
            'type' => $validated['type'] ?: null,
            'description' => $validated['description'] ?: null,
            'price' => $validated['price'] !== null && $validated['price'] !== '' ? (float) $validated['price'] : null,
            'billing' => $validated['billing'] ?: 'one-time',
            'validity' => $validated['validity'] ?: null,
            'command' => $validated['command'] ?: null,
            'features' => collect(explode("\n", $validated['features']))->map(fn ($f) => trim($f))->filter()->values()->all(),
            'install_commands' => collect(explode("\n", $validated['installCommands']))->map(fn ($c) => trim($c))->filter()->values()->all(),
            'repo_url' => $validated['repoUrl'] ?: null,
            'docs_url' => $validated['docsUrl'] ?: null,
            'sort_order' => $validated['sort_order'],
        ];

        if ($this->editingId) {
            $package = TechPackage::query()->findOrFail($this->editingId);
            $package->update($data);
            session()->flash('status', 'Package updated.');
        } else {
            $package = TechPackage::query()->create($data);
            ActivityLog::record('admin.package.created', auth()->user(), ['package' => $package->title]);
            session()->flash('status', 'Package created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(TechPackage $package): void
    {
        ActivityLog::record('admin.package.deleted', auth()->user(), ['package' => $package->title]);
        $package->delete();
        session()->flash('status', 'Package deleted.');
    }

    public function setStatus(TechPackage $package, string $status): void
    {
        if (! in_array($status, TechPackage::STATUSES, true)) {
            return;
        }

        $package->update(['publish_status' => $status]);
        ActivityLog::record('admin.package.status_changed', auth()->user(), ['package' => $package->title, 'status' => $status]);
        session()->flash('status', 'Package marked as '.ucfirst($status).'.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function packages()
    {
        return TechPackage::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->packages->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return TechPackage::query()->count();
    }

    private function resetForm(): void
    {
        $this->reset('icon', 'title', 'version', 'type', 'description', 'price', 'command', 'features', 'installCommands', 'repoUrl', 'docsUrl', 'sort_order');
        $this->category = 'javascript';
        $this->billing = 'one-time';
        $this->validity = '30 Days';
    }

    public function render()
    {
        return view('livewire.admin.packages');
    }
}
