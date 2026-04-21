<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_thread()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $thread = Thread::factory()->create([
            'creator_only_comments' => false
        ]);

        $this->actingAs($user)->post(
            route('posts.store', $thread->slug),
            [
                'body' => 'Test comment'
            ]
        );

        $this->assertDatabaseHas('posts', [
            'body' => 'Test comment'
        ]);
    }

    public function test_user_can_comment_on_thread_with_rich_text_html_body()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $thread = Thread::factory()->create([
            'creator_only_comments' => false
        ]);

        $richBody = '<p><strong>Rich text</strong> comment body with <em>formatting</em>.</p>';

        $this->actingAs($user)->post(
            route('posts.store', $thread->slug),
            [
                'body' => $richBody
            ]
        )->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $richBody,
        ]);
    }

    public function test_author_can_update_comment()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $thread = Thread::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'thread_id' => $thread->id
        ]);

        $this->actingAs($user)->patch(
            route('posts.update', [$thread->slug, $post->id]),
            [
                'body' => 'Updated comment'
            ]
        );

        $this->assertDatabaseHas('posts', [
            'body' => 'Updated comment'
        ]);
    }

    public function test_non_owner_cannot_update_comment()
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $thread = Thread::factory()->for($owner)->create();
        $post = Post::factory()->for($thread)->for($owner)->create();

        $this->actingAs($otherUser)->patch(
            route('posts.update', [$thread->slug, $post->id]),
            [
                'body' => 'Unauthorized edit',
            ]
        )->assertStatus(403);
    }

    public function test_admin_can_update_another_users_comment()
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $thread = Thread::factory()->for($owner)->create();
        $post = Post::factory()->for($thread)->for($owner)->create();

        $this->actingAs($admin)->patch(
            route('posts.update', [$thread->slug, $post->id]),
            [
                'body' => 'Admin updated comment',
            ]
        )->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'body' => 'Admin updated comment',
        ]);
    }

   public function test_author_can_delete_comment()
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->create();
        $post = Post::factory()->for($thread)->for($user)->create();

        $this->actingAs($user)
            ->delete(route('posts.destroy', [$thread->slug, $post->id]))
            ->assertRedirect(); // optional: check redirect

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_non_owner_cannot_delete_comment()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $thread = Thread::factory()->for($owner)->create();
        $post = Post::factory()->for($thread)->for($owner)->create();

        $this->actingAs($otherUser)
            ->delete(route('posts.destroy', [$thread->slug, $post->id]))
            ->assertStatus(403);
    }

    public function test_admin_can_delete_another_users_comment()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $thread = Thread::factory()->for($owner)->create();
        $post = Post::factory()->for($thread)->for($owner)->create();

        $this->actingAs($admin)
            ->delete(route('posts.destroy', [$thread->slug, $post->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_only_thread_owner_can_comment_when_creator_only_comments_enabled()
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $thread = Thread::factory()->for($owner)->create([
            'creator_only_comments' => true,
        ]);

        $this->actingAs($otherUser)
            ->post(route('posts.store', $thread->slug), [
                'body' => 'I should not be able to comment',
            ])
            ->assertStatus(403);

        $this->actingAs($owner)
            ->post(route('posts.store', $thread->slug), [
                'body' => 'Thread owner can comment',
            ])
            ->assertRedirect();
    }
}