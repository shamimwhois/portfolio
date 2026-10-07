<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Payments;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualMethodsCardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_manual_methods_render_compact_gateway_style_cards(): void
    {
        $html = Livewire::actingAs($this->admin())
            ->test(Payments::class)
            ->html();

        $this->assertStringContainsString('Manual methods', $html);

        // Gateway-style card anatomy: avatar initials + type + status badge + account summary + footer Configure button.
        $this->assertStringContainsString('Configure', $html);
        $this->assertStringContainsString('preview:', $html); // Alpine live-preview mirror on each card
        $this->assertStringContainsString('x-model="preview.label"', $html);
        $this->assertStringContainsString('x-model="preview.type"', $html);
        $this->assertStringContainsString('x-model="preview.account"', $html);
        // Persistence half: debounced input sync + immediate blur/change sync to Livewire state.
        $this->assertStringContainsString('@input.debounce.400ms="$wire.set(\'payments.methods', $html);
        $this->assertStringContainsString('@change="$wire.set(\'payments.methods', $html);
        $this->assertStringContainsString('x-text="preview.label"', $html);
        $this->assertStringContainsString('x-text="preview.type"', $html);
        $this->assertStringContainsString('preview.account || \'No account set\'', $html);
        $this->assertStringContainsString('x-show="open"', $html);
        $this->assertStringContainsString('x-collapse', $html); // smooth height animation via @alpinejs/collapse
        $this->assertStringContainsString('x-text="open ? \'Close\' : \'Configure\'"', $html);
        $this->assertStringContainsString('Enabled at checkout', $html);

        // Collapsed by default: fields hidden behind x-show (x-cloak present so they don't flash).
        $this->assertStringContainsString('x-cloak', $html);

        // The summary rows exist (account + checkout visibility toggle).
        $this->assertStringContainsString('Visible at checkout', $html);
    }
}
