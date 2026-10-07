<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Faq;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Faqs extends Component
{
    /** @var int FAQs per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $question = '';

    public string $answer = '';

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(Faq $faq): void
    {
        $this->resetForm();
        $this->editingId = $faq->id;
        $this->showForm = true;
        $this->question = $faq->question;
        $this->answer = $faq->answer;
        $this->sort_order = $faq->sort_order;
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
            'question' => ['required', 'string', 'max:300'],
            'answer' => ['required', 'string', 'max:3000'],
            'sort_order' => ['integer'],
        ]);

        if ($this->editingId) {
            $faq = Faq::query()->findOrFail($this->editingId);
            $faq->update($validated);
            session()->flash('status', 'FAQ updated.');
        } else {
            $faq = Faq::query()->create($validated);
            ActivityLog::record('admin.faq.created', auth()->user(), ['faq' => $faq->question]);
            session()->flash('status', 'FAQ created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Faq $faq): void
    {
        ActivityLog::record('admin.faq.deleted', auth()->user(), ['faq' => $faq->question]);
        $faq->delete();
        session()->flash('status', 'FAQ deleted.');
    }

    public function setStatus(Faq $faq, string $status): void
    {
        if (! in_array($status, Faq::STATUSES, true)) {
            return;
        }

        $faq->update(['publish_status' => $status]);
        ActivityLog::record('admin.faq.status_changed', auth()->user(), ['faq' => $faq->question, 'status' => $status]);
        session()->flash('status', 'FAQ marked as '.ucfirst($status).'.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function faqs()
    {
        return Faq::query()->orderBy('sort_order')->limit($this->limit + 1)->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->faqs->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return Faq::query()->count();
    }

    private function resetForm(): void
    {
        $this->reset('question', 'answer', 'sort_order');
    }

    public function render()
    {
        return view('livewire.admin.faqs');
    }
}
