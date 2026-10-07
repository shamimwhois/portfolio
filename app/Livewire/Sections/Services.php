<?php

namespace App\Livewire\Sections;

use App\Livewire\Concerns\InteractsWithPlanCheckout;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Services extends Component
{
    use InteractsWithPlanCheckout;

    public int $categoryId;

    public function mount(): void
    {
        $first = ServiceCategory::query()->published()->orderBy('sort_order')->value('id');

        $this->categoryId = $first ?? 0;
    }

    public function setCategory(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->message = null;
        $this->closeCheckout();
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::query()->published()->with(['tiers' => fn ($q) => $q->published()])->orderBy('sort_order')->get();
    }

    #[Computed]
    public function tiers()
    {
        return ServiceTier::query()
            ->published()
            ->where('service_category_id', $this->categoryId)
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return view('livewire.sections.services');
    }
}
