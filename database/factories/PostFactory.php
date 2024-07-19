<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name,
            'slug' => Str::slug($title),
            'summary' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(['draft', 'published', 'scheduled', 'archived']),
            'published_at' => $this->faker->dateTimeThisYear(),
            'views_count' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
