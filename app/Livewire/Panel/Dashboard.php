<?php

namespace App\Livewire\Panel;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.panel.dashboard', [
            'stats' => [
                [
                    'label' => 'Invoices',
                    'value' => $user->invoices()->count(),
                    'icon' => 'receipt',
                ],
                [
                    'label' => 'Total Paid',
                    'value' => '$'.number_format($user->invoices()->where('status', 'paid')->sum('amount'), 2),
                    'icon' => 'cash',
                ],
                [
                    'label' => 'Active Services',
                    'value' => $user->invoices()->where('status', 'paid')->count(),
                    'icon' => 'layers',
                ],
                [
                    'label' => 'Support Tickets',
                    'value' => ContactMessage::query()->where('user_id', $user->id)->count(),
                    'icon' => 'chat',
                ],
            ],
            'recentInvoices' => $user->invoices()
                ->with('tier.category', 'package')
                ->latest()
                ->limit(5)
                ->get(),
            'reminders' => $user->clientProjects()
                ->whereNotNull('remind_at')
                ->where('remind_at', '<=', now())
                ->whereIn('status', ['pending', 'in_progress', 'review'])
                ->latest('remind_at')
                ->limit(3)
                ->get(),
            'latestPosts' => BlogPost::query()
                ->where('published', true)
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
