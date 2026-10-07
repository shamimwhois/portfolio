<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'excerpt' => fake()->paragraph(2),
            'body' => fake()->paragraphs(5, true),
            'tags' => fake()->randomElements(['Laravel', 'Livewire', 'Vue', 'PHP', 'Design', 'AI'], 3),
            'category' => fake()->randomElement(BlogPost::CATEGORIES),
            'views' => fake()->numberBetween(0, 5000),
            'cover_image' => null,
            'featured' => fake()->boolean(20),
            'published' => true,
            'published_at' => now()->subHours(fake()->numberBetween(1, 720)),
        ];
    }
}
