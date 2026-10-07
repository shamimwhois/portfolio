<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithPlanCheckout;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Plans extends Component
{
    use InteractsWithPlanCheckout;

    public string $category = 'all';

    public string $search = '';

    public function setCategory(string $category): void
    {
        if ($category === 'all' || ServiceCategory::query()->published()->whereKey($category)->exists()) {
            $this->category = $category;
            $this->message = null;
            $this->closeCheckout();
        }
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::query()
            ->published()
            ->with(['tiers' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Categories grouped by ServiceCategory ID. Each category is a section
     * header showing its plan count, with its plan cards beneath — the layout
     * of the reference marketplace image.
     */
    #[Computed]
    public function groups()
    {
        return $this->categories
            ->filter(fn ($category) => $this->category === 'all' || (string) $category->id === $this->category)
            ->map(function ($category) {
                $tiers = $category->tiers->when($this->search, fn ($collection) => $collection->filter(
                    fn (ServiceTier $tier) => str_contains(strtolower($tier->name), strtolower($this->search))
                        || str_contains(strtolower(implode(' ', $tier->features ?? [])), strtolower($this->search))
                ));

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $tiers->count(),
                    'tiers' => $tiers->values(),
                ];
            })
            ->filter(fn ($group) => $group['count'] > 0)
            ->values();
    }

    public function render()
    {
        return view('livewire.plans');
    }
}
