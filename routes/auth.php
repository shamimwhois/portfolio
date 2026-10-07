<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Social login (OAuth2) — Google + GitHub.
    Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])
        ->whereIn('provider', ['google', 'github'])
        ->name('auth.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->whereIn('provider', ['google', 'github'])
        ->name('auth.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated session actions
|--------------------------------------------------------------------------
*/
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

});