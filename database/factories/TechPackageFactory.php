<?php

namespace Database\Factories;

use App\Models\TechPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TechPackage>
 */
class TechPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(TechPackage::CATEGORIES),
            'icon' => Str::upper(Str::substr(fake()->word(), 0, 2)),
            'title' => fake()->unique()->words(3, true),
            'version' => fake()->numerify('#.#.#'),
            'type' => fake()->randomElement(['package', 'tool', 'plugin']),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 200),
            'billing' => fake()->randomElement(TechPackage::BILLINGS),
            'validity' => fake()->randomElement(TechPackage::VALIDITIES),
            'command' => 'npm install '.Str::lower(Str::slug(fake()->word())),
            'features' => fake()->randomElements(['Auto-updates', 'Priority support', 'Custom config', 'Docs access'], 3),
            'install_commands' => ['composer require '.Str::lower(Str::slug(fake()->word()))],
            'repo_url' => 'https://github.com/example/'.Str::lower(Str::slug(fake()->word())),
            'docs_url' => 'https://docs.example.com/'.Str::lower(Str::slug(fake()->word())),
            'sort_order' => 0,
            'publish_status' => TechPackage::STATUS_PUBLISHED,
        ];
    }
}
