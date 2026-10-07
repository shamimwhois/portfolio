<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Services extends Component
{
    /** @var int Categories per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showCategoryForm = false;

    public bool $showTierForm = false;

    public ?int $editingCategoryId = null;

    public string $categoryName = '';

    public string $categorySlug = '';

    public int $categorySort = 0;

    public ?int $editingTierId = null;

    public ?int $tierCategoryId = null;

    public string $tierName = '';

    public string $tierPrice = '';

    public string $tierBilling = 'one-time';

    public string $tierFeatures = '';

    public bool $tierPopular = false;

    public string $tierCta = 'outline';

    public int $tierSort = 0;

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function categories()
    {
        return ServiceCategory::query()->with('tiers')->orderBy('sort_order')->limit($this->limit + 1)->get();
    }

    /**
     * Every category — used by the tier-form dropdown so plans can be
     * assigned to categories beyond the paginated list.
     */
    #[Computed]
    public function allCategories()
    {
        return ServiceCategory::query()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->categories->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return ServiceCategory::query()->count();
    }

    /* ---------- Category actions ---------- */

    public function createCategory(): void
    {
        $this->resetCategoryForm();
        $this->editingCategoryId = null;
        $this->showCategoryForm = true;
        $this->showTierForm = false;
    }

    public function editCategory(ServiceCategory $category): void
    {
        $this->resetCategoryForm();
        $this->editingCategoryId = $category->id;
        $this->showCategoryForm = true;
        $this->categoryName = $category->name;
        $this->categorySlug = $category->slug;
        $this->categorySort = $category->sort_order;
    }

    /**
     * Unified cancel — used by the modal close button / Escape and the
     * modal-actions Cancel button. Closes whichever form is open so a
     * generic `cancel` call can never 500 on this page.
     */
    public function cancel(): void
    {
        $this->editingCategoryId = null;
        $this->editingTierId = null;
        $this->showCategoryForm = false;
        $this->showTierForm = false;
        $this->resetCategoryForm();
        $this->resetTierForm();
    }

    public function cancelCategory(): void
    {
        $this->cancel();
    }

    public function saveCategory(): void
    {
        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:120'],
            'categorySlug' => ['required', 'alpha_dash', 'max:120', Rule::unique('service_categories', 'slug')->ignore($this->editingCategoryId)],
            'categorySort' => ['integer'],
        ]);

        $data = [
            'name' => $validated['categoryName'],
            'slug' => $validated['categorySlug'] ?: Str::slug($validated['categoryName']),
            'sort_order' => $validated['categorySort'],
        ];

        if ($this->editingCategoryId) {
            $category = ServiceCategory::query()->findOrFail($this->editingCategoryId);
            $category->update($data);
            session()->flash('status', 'Category updated.');
        } else {
            $category = ServiceCategory::query()->create($data);
            ActivityLog::record('admin.service_category.created', auth()->user(), ['category' => $category->name]);
            session()->flash('status', 'Category created.');
        }

        $this->editingCategoryId = null;
        $this->showCategoryForm = false;
        $this->resetCategoryForm();
    }

    public function deleteCategory(ServiceCategory $category): void
    {
        ActivityLog::record('admin.service_category.deleted', auth()->user(), ['category' => $category->name]);
        $category->delete();
        session()->flash('status', 'Category deleted.');
    }

    public function setCategoryStatus(ServiceCategory $category, string $status): void
    {
        if (! in_array($status, ServiceCategory::STATUSES, true)) {
            return;
        }

        $category->update(['publish_status' => $status]);
        ActivityLog::record('admin.service_category.status_changed', auth()->user(), ['category' => $category->name, 'status' => $status]);
        session()->flash('status', 'Category marked as '.ucfirst($status).'.');
    }

    public function setTierStatus(ServiceTier $tier, string $status): void
    {
        if (! in_array($status, ServiceTier::STATUSES, true)) {
            return;
        }

        $tier->update(['publish_status' => $status]);
        ActivityLog::record('admin.service_tier.status_changed', auth()->user(), ['tier' => $tier->name, 'status' => $status]);
        session()->flash('status', 'Plan marked as '.ucfirst($status).'.');
    }

    /* ---------- Tier actions ---------- */

    public function createTier(int $categoryId): void
    {
        $this->resetTierForm();
        $this->editingTierId = null;
        $this->tierCategoryId = $categoryId;
        $this->showTierForm = true;
        $this->showCategoryForm = false;
    }

    public function editTier(ServiceTier $tier): void
    {
        $this->resetTierForm();
        $this->editingTierId = $tier->id;
        $this->tierCategoryId = $tier->service_category_id;
        $this->showTierForm = true;
        $this->tierName = $tier->name;
        $this->tierPrice = $tier->price;
        $this->tierBilling = $tier->billing;
        $this->tierFeatures = implode("\n", $tier->features ?? []);
        $this->tierPopular = (bool) $tier->popular;
        $this->tierCta = $tier->cta;
        $this->tierSort = $tier->sort_order;
    }

    public function cancelTier(): void
    {
        $this->editingTierId = null;
        $this->showTierForm = false;
        $this->resetTierForm();
    }

    public function saveTier(): void
    {
        $validated = $this->validate([
            'tierCategoryId' => ['required', 'exists:service_categories,id'],
            'tierName' => ['required', 'string', 'max:120'],
            'tierPrice' => ['required', 'string', 'max:40'],
            'tierBilling' => ['required', Rule::in(['one-time', 'month'])],
            'tierFeatures' => ['nullable', 'string', 'max:2000'],
            'tierPopular' => ['boolean'],
            'tierCta' => ['required', Rule::in(['outline', 'gradient'])],
            'tierSort' => ['integer'],
        ]);

        $data = [
            'service_category_id' => $validated['tierCategoryId'],
            'name' => $validated['tierName'],
            'price' => $validated['tierPrice'],
            'billing' => $validated['tierBilling'],
            'features' => collect(explode("\n", $validated['tierFeatures']))
                ->map(fn ($f) => trim($f))
                ->filter()
                ->values()
                ->all(),
            'popular' => $validated['tierPopular'],
            'cta' => $validated['tierCta'],
            'sort_order' => $validated['tierSort'],
        ];

        if ($this->editingTierId) {
            $tier = ServiceTier::query()->findOrFail($this->editingTierId);
            $tier->update($data);
            session()->flash('status', 'Plan updated.');
        } else {
            $tier = ServiceTier::query()->create($data);
            ActivityLog::record('admin.service_tier.created', auth()->user(), ['tier' => $tier->name]);
            session()->flash('status', 'Plan created.');
        }

        $this->editingTierId = null;
        $this->showTierForm = false;
        $this->resetTierForm();
    }

    public function deleteTier(ServiceTier $tier): void
    {
        ActivityLog::record('admin.service_tier.deleted', auth()->user(), ['tier' => $tier->name]);
        $tier->delete();
        session()->flash('status', 'Plan deleted.');
    }

    private function resetCategoryForm(): void
    {
        $this->reset('categoryName', 'categorySlug', 'categorySort');
    }

    private function resetTierForm(): void
    {
        $this->reset('tierName', 'tierPrice', 'tierBilling', 'tierFeatures', 'tierPopular', 'tierCta', 'tierSort');
        $this->tierBilling = 'one-time';
        $this->tierCta = 'outline';
    }

    public function render()
    {
        return view('livewire.admin.services');
    }
}
