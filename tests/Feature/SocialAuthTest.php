<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\SocialAuthController;
use App\Models\User;
use App\Support\SocialAuth;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function enableProvider(string $provider): void
    {
        config([
            'services.'.$provider.'.client_id' => 'client-'.$provider,
            'services.'.$provider.'.client_secret' => 'secret-'.$provider,
            'services.'.$provider.'.redirect' => null,
        ]);
    }

    private function makeRequest(string $provider, array $query = []): Request
    {
        $request = Request::create('/auth/'.$provider.'/callback', 'GET', $query);

        $request->setLaravelSession($this->app['session']->driver());

        return $request;
    }

    public function test_available_only_returns_configured_providers(): void
    {
        $this->assertEmpty(SocialAuth::available());

        $this->enableProvider('google');

        $this->assertSame(['google'], array_keys(SocialAuth::available()));
    }

    public function test_redirect_bounces_to_provider_when_configured(): void
    {
        $this->enableProvider('github');

        $response = app(SocialAuthController::class)->redirect(new Request(), 'github');

        $this->assertStringContainsString('https://github.com/login/oauth/authorize', $response->getTargetUrl());
        $this->assertStringContainsString('client_id=client-github', $response->getTargetUrl());
        $this->assertNotEmpty(session('social_auth_state'));
    }

    public function test_redirect_unconfigured_goes_back_to_login(): void
    {
        $response = app(SocialAuthController::class)->redirect(new Request(), 'google');

        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertEmpty(session('social_auth_state'));
    }

    public function test_callback_creates_user_and_logs_in(): void
    {
        $this->enableProvider('github');

        session()->put('social_auth_state', 'expected-state');
        session()->put('social_auth_provider', 'github');

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response(['access_token' => 'token-123']),
            'https://api.github.com/user' => Http::response([
                'id' => 42,
                'login' => 'octocat',
                'name' => 'Octo Cat',
                'email' => 'octo@example.com',
                'avatar_url' => 'https://avatars.example.com/octo.png',
            ]),
        ]);

        $response = app(SocialAuthController::class)->callback(
            $this->makeRequest('github', ['code' => 'code-123', 'state' => 'expected-state']),
            'github'
        );

        $this->assertSame(route('panel.dashboard'), $response->getTargetUrl());
        $this->assertDatabaseHas('users', ['email' => 'octo@example.com', 'role' => User::ROLE_CUSTOMER]);
        $this->assertAuthenticated();
        $this->assertSame('Octo Cat', auth()->user()->name);
    }

    public function test_callback_logs_in_existing_user(): void
    {
        $this->enableProvider('google');

        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);

        session()->put('social_auth_state', 'expected-state');
        session()->put('social_auth_provider', 'google');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token-123']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'g-1',
                'name' => 'Existing User',
                'email' => 'existing@example.com',
                'picture' => 'https://avatars.example.com/g.png',
            ]),
        ]);

        $response = app(SocialAuthController::class)->callback(
            $this->makeRequest('google', ['code' => 'code-123', 'state' => 'expected-state']),
            'google'
        );

        $this->assertSame(route('panel.dashboard'), $response->getTargetUrl());
        $this->assertSame($existing->id, auth()->id());
        $this->assertSame(1, User::query()->where('email', 'existing@example.com')->count());
    }

    public function test_callback_rejects_mismatched_state(): void
    {
        $this->enableProvider('google');

        session()->put('social_auth_state', 'expected-state');
        session()->put('social_auth_provider', 'google');

        $response = app(SocialAuthController::class)->callback(
            $this->makeRequest('google', ['code' => 'code-123', 'state' => 'forged-state']),
            'google'
        );

        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new@social.test']);
    }

    public function test_callback_rejects_provider_error(): void
    {
        $this->enableProvider('google');

        session()->put('social_auth_state', 'expected-state');
        session()->put('social_auth_provider', 'google');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $response = app(SocialAuthController::class)->callback(
            $this->makeRequest('google', ['code' => 'bad-code', 'state' => 'expected-state']),
            'google'
        );

        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertGuest();
    }
}
