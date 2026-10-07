<?php

namespace App\Livewire\Home;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\TimelinePhase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Index extends Component
{
    /** @var int Featured projects rendered in the Selected work grid — Load more steps by 6 (grid is 3-col). */
    public int $projectLimit = 6;

    public function loadMoreProjects(): void
    {
        $this->projectLimit += 6;
    }

    public function render()
    {
        return view('livewire.home.index', [
            'settings' => Setting::all()->pluck('value', 'key'),
            'skills' => Skill::query()->published()->orderBy('sort_order')->get(),
            'projects' => Project::query()->published()->where('featured', true)->orderBy('sort_order')->limit($this->projectLimit + 1)->get(),
            'projectsTotal' => Project::query()->published()->where('featured', true)->count(),
            'projectLimit' => $this->projectLimit,
            'phases' => TimelinePhase::query()->published()->orderBy('sort_order')->get(),
            'faqs' => Faq::query()->published()->orderBy('sort_order')->get(),
            'stats' => Setting::get('stats', []),
            'typewriterLines' => Setting::get('typewriter_lines', []),
        ]);
    }
}
