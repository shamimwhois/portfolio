<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Admin\Blog;
use App\Livewire\Admin\ClientProjects;
use App\Livewire\Admin\Conversations;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\Feed;
use App\Livewire\Admin\GroupPackages;
use App\Livewire\Admin\Invoices;
use App\Livewire\Admin\Messages;
use App\Livewire\Admin\Packages;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Services;
use App\Livewire\Admin\Settings\Ai;
use App\Livewire\Admin\Settings\Appearance;
use App\Livewire\Admin\Settings\Integrations;
use App\Livewire\Admin\Settings\Payments;
use App\Livewire\Admin\Settings\Seo;
use App\Livewire\Admin\Settings\Site;
use App\Livewire\Admin\Settings\Social;
use App\Livewire\Admin\Skills;
use App\Livewire\Admin\Timeline;
use App\Livewire\Admin\Users;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

/*
|--------------------------------------------------------------------------
| Admin console
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin', EnsureUserIsActive::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('/users', Users::class)->name('users');
        Route::get('/projects', Projects::class)->name('projects');
        Route::get('/client-projects', ClientProjects::class)->name('client-projects');
        Route::get('/skills', Skills::class)->name('skills');
        Route::get('/services', Services::class)->name('services');
        Route::get('/packages', Packages::class)->name('packages');
        Route::get('/group-packages', GroupPackages::class)->name('group-packages');
        Route::get('/blog', Blog::class)->name('blog');
        Route::get('/feed', Feed::class)->name('feed');
        Route::get('/faqs', Faqs::class)->name('faqs');
        Route::get('/invoices', Invoices::class)->name('invoices');
        Route::get('/timeline', Timeline::class)->name('timeline');
        Route::get('/messages', Messages::class)->name('messages');
        Route::get('/conversations', Conversations::class)->name('conversations');
        Route::get('/settings', fn () => redirect()->route('admin.settings.site'))->name('settings');
        Route::get('/settings/site', Site::class)->name('settings.site');
        Route::get('/settings/seo', Seo::class)->name('settings.seo');
        Route::get('/settings/appearance', Appearance::class)->name('settings.appearance');
        Route::get('/settings/ai', Ai::class)->name('settings.ai');
        Route::get('/settings/payments', Payments::class)->name('settings.payments');
        Route::get('/settings/social', Social::class)->name('settings.social');
        Route::get('/settings/integrations', Integrations::class)->name('settings.integrations');
    });

});