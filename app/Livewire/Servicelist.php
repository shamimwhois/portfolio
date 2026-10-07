<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithPackageCheckout;
use App\Models\TechPackage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Servicelist extends Component
{
    use InteractsWithPackageCheckout;

    public string $search = '';

    public string $category = 'all';

    /** @var array<int, string> packageId => chosen validity */
    public array $validities = [];

    /** Visible items per render — expanded via Load more so the page stays fast. */
    public int $limit = 9;

    public function setCategory(string $category): void
    {
        if ($category === 'all' || in_array($category, TechPackage::CATEGORIES, true)) {
            $this->category = $category;
            $this->limit = 9;
            $this->message = null;
            $this->closeCheckout();
        }
    }

    public function updatedSearch(): void
    {
        $this->limit = 9;
    }

    public function loadMore(): void
    {
        $this->limit += 9;
    }

    /**
     * Published tools are Active; unpublished tools stay listed but are shown
     * as Inactive / Stock Out (drafts are hidden entirely).
     */
    #[Computed]
    public function packages()
    {
        // limit + 1 doubles as the "are there more?" sentinel.
        return TechPackage::query()
            ->whereIn('publish_status', [TechPackage::STATUS_PUBLISHED, TechPackage::STATUS_UNPUBLISHED])
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')
                ->orWhere('category', 'like', '%'.$this->search.'%')))
            ->orderBy('sort_order')
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->packages->count() > $this->limit;
    }

    /**
     * Total packages matching the current filters — shown on the Load more
     * control so visitors know how much is left.
     */
    #[Computed]
    public function matchingTotal(): int
    {
        return TechPackage::query()
            ->whereIn('publish_status', [TechPackage::STATUS_PUBLISHED, TechPackage::STATUS_UNPUBLISHED])
            ->when($this->category !== 'all', fn ($q) => $q->where('category', $this->category))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')
                ->orWhere('category', 'like', '%'.$this->search.'%')))
            ->count();
    }

    /**
     * Packages grouped into category sections (with counts) so the marketplace
     * reads like the reference image: a header per category, cards beneath.
     *
     * @return array<int, array{key: string, label: string, count: int, items: mixed}>
     */
    #[Computed]
    public function groups()
    {
        // Drop the +1 sentinel before grouping.
        $items = $this->packages->take($this->limit)->groupBy('category');

        return collect($this->categories)
            ->filter(fn ($label, $key) => $items->has($key))
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => $items[$key]->count(),
                'items' => $items[$key],
            ])
            ->values()
            ->all();
    }

    public function chooseValidity(int $packageId, string $validity): void
    {
        if (in_array($validity, TechPackage::VALIDITIES, true)) {
            $this->validities[$packageId] = $validity;
        }
    }

    protected function buildInvoiceNotes(TechPackage $package): ?string
    {
        $validity = $this->validities[$package->id] ?? $package->validity;
        $notes = trim($this->notes);

        return trim('Validity: '.$validity.($notes ? ' | '.$notes : ''));
    }

    protected function purchaseActivityKey(): string
    {
        return 'servicelist.purchased';
    }

    #[Computed]
    public function categories()
    {
        return array_combine(
            TechPackage::CATEGORIES,
            ['JavaScript', 'PHP', 'Python', 'Package Managers']
        );
    }

    /**
     * Per-category counts of visible (published + unpublished) tools,
     * used to badge the filter tabs like the reference marketplace.
     */
    #[Computed]
    public function tabCounts()
    {
        return TechPackage::query()
            ->whereIn('publish_status', [TechPackage::STATUS_PUBLISHED, TechPackage::STATUS_UNPUBLISHED])
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
    }

    public function render()
    {
        return view('livewire.servicelist');
    }
}
