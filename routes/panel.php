<?php

use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMediaController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Panel\Dashboard;
use App\Livewire\Panel\Invoices;
use App\Livewire\Panel\Order;
use App\Livewire\Panel\Profile;
use App\Livewire\Panel\ProjectDetail;
use App\Livewire\Panel\Projects;
use App\Livewire\Panel\Services;
use App\Livewire\Panel\Support;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

/*
|--------------------------------------------------------------------------
| Authenticated customer panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureUserIsActive::class])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/invoices', Invoices::class)->name('invoices');
    Route::get('/projects', Projects::class)->name('projects');
    Route::get('/projects/{project}', ProjectDetail::class)->name('projects.show');
    Route::get('/projects/{project}/files/{file}', ProjectFileController::class)->name('projects.file');
    Route::get('/projects/{project}/media/{media}', ProjectMediaController::class)->name('projects.media');
    Route::get('/orders/new', Order::class)->name('orders.create');
    Route::get('/orders/{project}/edit', Order::class)->name('orders.edit');
    Route::get('/services', Services::class)->name('services');
    Route::get('/support', Support::class)->name('support');
    Route::get('/profile', Profile::class)->name('profile');
});

});