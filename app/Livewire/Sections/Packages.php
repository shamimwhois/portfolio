<?php

namespace App\Livewire\Sections;

use App\Livewire\Concerns\InteractsWithPackageCheckout;
use App\Models\TechPackage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Packages extends Component
{
    use InteractsWithPackageCheckout;

    public string $category = 'javascript';

    public function setCategory(string $category): void
    {
        if (in_array($category, TechPackage::CATEGORIES, true)) {
            $this->category = $category;
            $this->message = null;
            $this->closeCheckout();
        }
    }

    public function render()
    {
        return view('livewire.sections.packages', [
            'categories' => array_combine(
                TechPackage::CATEGORIES,
                ['JavaScript', 'PHP', 'Python', 'Package Managers']
            ),
            'packages' => TechPackage::query()
                ->published()
                ->where('category', $this->category)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
