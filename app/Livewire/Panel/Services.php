<?php

namespace App\Livewire\Panel;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Services extends Component
{
    #[Computed]
    public function activeServices()
    {
        return auth()->user()
            ->invoices()
            ->with('tier.category', 'package')
            ->where('status', 'paid')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.services');
    }
}
