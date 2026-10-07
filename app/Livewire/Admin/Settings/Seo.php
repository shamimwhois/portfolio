<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\Seo as SeoSupport;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Seo extends Component
{
    public string $search = '';

    public ?string $editingKey = null; // global | route name | custom slug

    public bool $isNew = false;

    public string $editPath = '';

    public string $editTitle = '';

    public string $editDescription = '';

    public string $editRobots = 'index,follow';

    public string $editOgImage = '';

    public function manage(string $key): void
    {
        $row = SeoSupport::rows()[$key] ?? null;

        if (! $row) {
            return;
        }

        $this->editingKey = $key;
        $this->isNew = false;
        $this->editPath = (string) ($row['path'] ?? '');
        $this->editTitle = (string) ($row['meta_title'] ?? '');
        $this->editDescription = (string) ($row['meta_description'] ?? '');
        $this->editRobots = (string) ($row['robots'] ?? 'index,follow');
        $this->editOgImage = (string) ($row['og_image'] ?? '');
    }

    public function addPage(): void
    {
        $this->editingKey = null;
        $this->isNew = true;
        $this->editPath = '';
        $this->editTitle = '';
        $this->editDescription = '';
        $this->editRobots = 'index,follow';
        $this->editOgImage = '';
    }

    public function closeModal(): void
    {
        $this->editingKey = null;
        $this->isNew = false;
    }

    public function savePage(): void
    {
        $this->validate([
            'editTitle' => ['nullable', 'string', 'max:160'],
            'editDescription' => ['nullable', 'string', 'max:320'],
            'editRobots' => ['required', Rule::in(array_keys(SeoSupport::ROBOTS))],
            'editOgImage' => ['nullable', 'string', 'max:500'],
            'editPath' => $this->isNew ? ['required', 'string', 'starts_with:/', 'max:120'] : ['nullable', 'string'],
        ]);

        if ($this->isNew) {
            foreach (SeoSupport::records() as $record) {
                if (($record['path'] ?? null) === $this->editPath) {
                    $this->addError('editPath', 'A record for this path already exists.');

                    return;
                }
            }
        }

        $payload = [
            'meta_title' => filled($this->editTitle) ? $this->editTitle : null,
            'meta_description' => filled($this->editDescription) ? $this->editDescription : null,
            'robots' => $this->editRobots,
            'og_image' => filled($this->editOgImage) ? $this->editOgImage : null,
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($this->isNew) {
            $records = SeoSupport::records();
            $key = 'custom_'.Str::slug(trim($this->editPath, '/'));
            $records[$key] = ['path' => $this->editPath, ...$payload];
            Setting::set('seo_pages', $records);
        } elseif ($this->editingKey === 'global') {
            Setting::set('seo', array_replace(Setting::get('seo', []), $payload));
        } else {
            $records = SeoSupport::records();
            $records[$this->editingKey] = $payload;
            Setting::set('seo_pages', $records);
        }

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'SEO saved — it applies to the public site immediately.');
        $this->closeModal();
    }

    public function deletePage(string $key): void
    {
        $records = SeoSupport::records();

        if (! isset($records[$key])) {
            return;
        }

        unset($records[$key]);
        Setting::set('seo_pages', $records);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'SEO record removed.');
        $this->closeModal();
    }

    /**
     * Table rows narrowed by the search box.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function filteredRows(): array
    {
        if (! $this->search) {
            return SeoSupport::rows();
        }

        $needle = strtolower($this->search);

        return collect(SeoSupport::rows())
            ->filter(fn (array $row, string $key) => str_contains(
                strtolower($row['label'].' '.($row['description'] ?? '').' '.($row['meta_title'] ?? '').' '.$key),
                $needle
            ))
            ->all();
    }

    /**
     * Registry summary for the stat strip.
     *
     * @return array{total: int, optimized: int, noindex: int}
     */
    #[Computed]
    public function metrics(): array
    {
        $rows = SeoSupport::rows();

        return [
            'total' => count($rows),
            'optimized' => collect($rows)->filter(
                fn (array $row) => filled($row['meta_title']) || filled($row['meta_description'])
            )->count(),
            'noindex' => collect($rows)->filter(
                fn (array $row) => filled($row['robots']) && str_starts_with($row['robots'], 'noindex')
            )->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.seo');
    }
}
