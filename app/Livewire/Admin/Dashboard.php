<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\Skill;
use App\Models\TechPackage;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $users = User::query()->count();
        $projects = Project::query()->count();
        $posts = BlogPost::query()->count();
        $revenue = Invoice::query()->where('status', 'paid')->sum('amount');

        return view('livewire.admin.dashboard', [
            'stats' => [
                ['label' => 'Total Users', 'value' => number_format($users), 'icon' => 'users', 'accent' => 'cyan', 'trend' => 'Active accounts'],
                ['label' => 'Projects', 'value' => number_format($projects), 'icon' => 'folder', 'accent' => 'violet', 'trend' => 'Portfolio live'],
                ['label' => 'Blog Posts', 'value' => number_format($posts), 'icon' => 'doc', 'accent' => 'magenta', 'trend' => 'Published nodes'],
                ['label' => 'Revenue', 'value' => '$'.number_format($revenue, 0), 'icon' => 'cash', 'accent' => 'cyan', 'trend' => 'Paid invoices'],
            ],
            'counts' => [
                'skills' => Skill::query()->count(),
                'services' => ServiceCategory::query()->count(),
                'packages' => TechPackage::query()->count(),
            ],
            'unreadMessages' => ContactMessage::query()->where('is_read', false)->count(),
            'totalMessages' => ContactMessage::query()->count(),
            'pendingInvoices' => Invoice::query()->where('status', 'pending')->count(),
            'pendingOrders' => Invoice::query()
                ->with('user', 'tier.category', 'package')
                ->where('status', 'pending')
                ->latest()
                ->limit(6)
                ->get(),
            'recentMessages' => ContactMessage::query()->latest()->limit(5)->get(),
            'recentActivity' => ActivityLog::query()->with('user')->latest()->limit(8)->get(),
        ]);
    }
}
