<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'body' => $this->faker->paragraphs(2, true),
            'user_id' => User::factory(),     // creates an author
            'thread_id' => Thread::factory(), // associates with a thread
            'edited_by_user_id' => null,      // optional editor
            'edited_at' => null,              // optional edit timestamp
        ];
    }

    /**
     * Optional state: mark post as edited
     */
    public function edited(User $editor = null)
    {
        return $this->state(fn(array $attributes) => [
            'edited_by_user_id' => $editor?->id ?? User::factory(),
            'edited_at' => now(),
        ]);
    }
}