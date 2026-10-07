<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithPackageCheckout;
use App\Models\GroupPackage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Servicegroup extends Component
{
    use InteractsWithPackageCheckout;

    public string $search = '';

    public string $kind = 'all';

    /** Visible items per render — expanded via Load more so the 62+ tool page stays fast. */
    public int $comboLimit = 6;

    public int $toolLimit = 12;

    public function setKind(string $kind): void
    {
        if ($kind === 'all' || in_array($kind, GroupPackage::KINDS, true)) {
            $this->kind = $kind;
            $this->comboLimit = 6;
            $this->toolLimit = 12;
            $this->message = null;
            $this->closeCheckout();
        }
    }

    public function updatedSearch(): void
    {
        $this->comboLimit = 6;
        $this->toolLimit = 12;
    }

    public function loadMoreCombos(): void
    {
        $this->comboLimit += 6;
    }

    public function loadMoreTools(): void
    {
        $this->toolLimit += 12;
    }

    /**
     * Combo packages (kind = combo). Published combos are Active; unpublished
     * ones stay listed but are shown as Inactive / Stock Out (drafts hidden).
     */
    #[Computed]
    public function combos()
    {
        return $this->visiblePackages(GroupPackage::KIND_COMBO, $this->comboLimit + 1);
    }

    /**
     * Single group-buy tools (kind = tool), same visibility rules.
     */
    #[Computed]
    public function tools()
    {
        return $this->visiblePackages(GroupPackage::KIND_TOOL, $this->toolLimit + 1);
    }

    private function visiblePackages(string $kind, int $limit)
    {
        // +1 sentinel for hasMore check; views use ->take(limit) to drop it.
        return GroupPackage::query()
            ->where('kind', $kind)
            ->whereIn('publish_status', [GroupPackage::STATUS_PUBLISHED, GroupPackage::STATUS_UNPUBLISHED])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    #[Computed]
    public function hasMoreCombos(): bool
    {
        return $this->combos->count() > $this->comboLimit;
    }

    #[Computed]
    public function hasMoreTools(): bool
    {
        return $this->tools->count() > $this->toolLimit;
    }

    /**
     * Real matching totals (the collections are capped), so the tab badges
     * and section headers stay accurate while items load progressively.
     */
    #[Computed]
    public function comboTotal(): int
    {
        return $this->visibleCount(GroupPackage::KIND_COMBO);
    }

    #[Computed]
    public function toolTotal(): int
    {
        return $this->visibleCount(GroupPackage::KIND_TOOL);
    }

    private function visibleCount(string $kind): int
    {
        return GroupPackage::query()
            ->where('kind', $kind)
            ->whereIn('publish_status', [GroupPackage::STATUS_PUBLISHED, GroupPackage::STATUS_UNPUBLISHED])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%')))
            ->count();
    }

    protected function checkoutModel(): string
    {
        return GroupPackage::class;
    }

    protected function checkoutForeignKey(): string
    {
        return 'group_package_id';
    }

    protected function checkoutCurrency(): string
    {
        return 'BDT';
    }

    protected function guestRedirectUrl(object $package): string
    {
        return route('register');
    }

    protected function purchaseActivityKey(): string
    {
        return 'servicegroup.purchased';
    }

    public function render()
    {
        return view('livewire.servicegroup');
    }
}
