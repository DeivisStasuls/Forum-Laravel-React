<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\User;
use App\Models\Subforum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition()
    {
        $title = $this->faker->sentence(6, true);

        return [
            'title' => $title,
            'body' => $this->faker->paragraphs(3, true),
            'creator_only_comments' => $this->faker->boolean(20), // 20% chance true
            'slug' => Str::slug($title) . '-' . Str::random(5),   // unique slug
            'user_id' => User::factory(),
            'subforum_id' => Subforum::factory(),
            'edited_by_user_id' => null,
            'edited_at' => null,
        ];
    }

    /**
     * Optional state: thread is restricted to creator only comments
     */
    public function creatorOnlyComments()
    {
        return $this->state(fn(array $attributes) => [
            'creator_only_comments' => true,
        ]);
    }

    /**
     * Optional state: thread already edited
     */
    public function edited(User $editor = null)
    {
        return $this->state(fn(array $attributes) => [
            'edited_by_user_id' => $editor?->id ?? User::factory(),
            'edited_at' => now(),
        ]);
    }
}