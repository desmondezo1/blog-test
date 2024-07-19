<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::factory()
        ->count(100)
        ->create()
        ->each(function ($post) {
            $post->tags()->attach(
                Tag::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
