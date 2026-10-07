<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(2),
            'link' => fake()->url(),
            'thumbnail' => null,
            'tech' => fake()->randomElements(['Laravel', 'Livewire', 'Vue', 'PHP', 'MySQL', 'Tailwind'], 3),
            'status' => fake()->randomElement(['Live', 'In Progress', 'Completed']),
            'featured' => true,
            'sort_order' => 0,
            'publish_status' => Project::STATUS_PUBLISHED,
        ];
    }
}
