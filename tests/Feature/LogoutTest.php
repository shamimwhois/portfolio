<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_logout_logs_activity(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.logout',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_post_logout(): void
    {
        $this->post('/logout')->assertRedirect('/login');
    }
}
