<?php

namespace Database\Factories;

use App\Models\GroupPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GroupPackage>
 */
class GroupPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => GroupPackage::KIND_TOOL,
            'title' => fake()->unique()->words(3, true),
            'icon' => Str::upper(Str::substr(fake()->word(), 0, 2)),
            'description' => fake()->paragraph(),
            'features' => fake()->randomElements(['Lifetime access', 'Free updates', 'Support', 'Docs'], 3),
            'price' => fake()->randomFloat(2, 3, 120),
            'billing' => fake()->randomElement(GroupPackage::BILLINGS),
            'publish_status' => GroupPackage::STATUS_PUBLISHED,
            'sort_order' => 0,
        ];
    }
}
