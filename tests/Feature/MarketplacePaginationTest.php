<?php

namespace Tests\Feature;

use App\Livewire\Servicelist;
use App\Livewire\Servicegroup;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketplacePaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_servicegroup_caps_tools_and_combos_with_load_more(): void
    {
        $component = Livewire::test(Servicegroup::class);

        // Totals stay accurate even though collections are capped.
        $this->assertSame(15, $component->get('comboTotal'));
        $this->assertSame(62, $component->get('toolTotal'));

        // limit + 1 sentinel: 6 combos / 12 tools max rendered at once.
        $this->assertLessThanOrEqual(7, $component->get('combos')->count());
        $this->assertLessThanOrEqual(13, $component->get('tools')->count());
        $this->assertTrue($component->get('hasMoreCombos'));
        $this->assertTrue($component->get('hasMoreTools'));

        $component->call('loadMoreTools');

        $this->assertSame(24, $component->get('toolLimit'));
        $this->assertLessThanOrEqual(25, $component->get('tools')->count());

        $component->call('loadMoreCombos');

        $this->assertSame(12, $component->get('comboLimit'));
        $this->assertLessThanOrEqual(13, $component->get('combos')->count());
    }

    public function test_servicegroup_search_resets_limits_and_filters(): void
    {
        $component = Livewire::test(Servicegroup::class);

        $component
            ->call('loadMoreTools')
            ->set('search', 'Ahrefs');

        $this->assertSame(12, $component->get('toolLimit'));
        $this->assertSame(6, $component->get('comboLimit'));
        $this->assertSame(1, $component->get('toolTotal'));
        $this->assertSame(1, $component->get('tools')->count());
        $this->assertFalse($component->get('hasMoreTools'));
    }

    public function test_servicelist_caps_packages_and_loads_more(): void
    {
        $component = Livewire::test(Servicelist::class);

        $this->assertLessThanOrEqual(10, $component->get('packages')->count());
        $this->assertSame(9, $component->get('matchingTotal'));
        $this->assertFalse($component->get('hasMore'));

        $component->call('loadMore');

        $this->assertSame(18, $component->get('limit'));
        $this->assertSame(9, $component->get('packages')->count());
    }

    public function test_servicelist_category_and_search_reset_limit(): void
    {
        $component = Livewire::test(Servicelist::class);

        $component->call('loadMore')->call('setCategory', 'php');

        $this->assertSame(9, $component->get('limit'));
        $this->assertSame(2, $component->get('packages')->count());

        $component->call('setCategory', 'all')->set('search', 'React');

        $this->assertSame(9, $component->get('limit'));
        $this->assertSame(1, $component->get('packages')->count());
    }
}
