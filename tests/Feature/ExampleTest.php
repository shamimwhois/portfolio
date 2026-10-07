<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_boots_with_expected_name(): void
    {
        $this->assertSame('Sci-Fi HUD Portfolio', config('app.name'));
    }
}
