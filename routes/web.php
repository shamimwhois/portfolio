<?php

use App\Http\Controllers\FeedMediaController;
use App\Livewire\Blog\Index as BlogIndex;
use App\Livewire\Blog\Show as BlogShow;
use App\Livewire\Contact;
use App\Livewire\Feed\Index as FeedIndex;
use App\Livewire\Home\Index as HomeIndex;
use App\Livewire\Plans;
use App\Livewire\Servicelist;
use App\Livewire\Servicegroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', HomeIndex::class)->name('home');

Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/{post:slug}', BlogShow::class)->name('blog.show');

Route::get('/contact', Contact::class)->name('contact');

Route::get('/feed', FeedIndex::class)->name('feed');

Route::get('/plan', Plans::class)->name('plan');

Route::get('/servicelist', Servicelist::class)->name('servicelist');

Route::get('/servicegroup', Servicegroup::class)->name('servicegroup');

Route::get('/feed/media/{media}', FeedMediaController::class)->name('feed.media');