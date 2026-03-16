<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Thread;
use App\Models\Subforum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_threads()
{
    $user = User::factory()->create();
    $thread = Thread::factory()->create();

    $this->actingAs($user)
         ->get(route('threads.show', $thread->slug))
         ->assertStatus(200)
         ->assertSee($thread->title);
}

    #[Test]
    public function authenticated_user_can_create_thread()
    {
        $user = User::factory()->create();
        $subforum = Subforum::factory()->create();

        $this->actingAs($user)
             ->post(route('threads.store'), [
                 'title' => 'Test Thread',
                 'body' => 'This is a test thread body.',
                 'subforum_id' => $subforum->id,
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('threads', [
            'title' => 'Test Thread',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function thread_author_can_update_thread()
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->for($user)->create();

        $this->actingAs($user)
     ->patch(route('threads.update', $thread->slug), [
         'title' => 'Updated Title',
         'body' => 'Updated body content.',
         'subforum_id' => $thread->subforum_id,
     ])
     ->assertRedirect();

        $this->assertDatabaseHas('threads', [
            'id' => $thread->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    public function non_author_cannot_update_thread()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $thread = Thread::factory()->for($otherUser)->create();

       $this->actingAs($user)
     ->patch(route('threads.update', $thread->slug), [
         'title' => 'Hack Attempt',
         'body' => 'Malicious content.',
         'subforum_id' => $thread->subforum_id,
     ])
     ->assertStatus(403);
    }

    #[Test]
    public function thread_author_can_delete_thread()
    {
        $user = User::factory()->create();
        $thread = Thread::factory()->for($user)->create();

        $this->actingAs($user)
             ->delete(route('threads.destroy', $thread->slug))
             ->assertRedirect();

        $this->assertDatabaseMissing('threads', [
            'id' => $thread->id,
        ]);
    }

    #[Test]
    public function non_author_cannot_delete_thread()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $thread = Thread::factory()->for($otherUser)->create();

        $this->actingAs($user)
             ->delete(route('threads.destroy', $thread->slug))
             ->assertStatus(403);

        $this->assertDatabaseHas('threads', [
            'id' => $thread->id,
        ]);
    }

    #[Test]
    public function forum_index_search_can_find_threads_by_author_name()
    {
        $viewer = User::factory()->create();
        $matchingAuthor = User::factory()->create(['name' => 'Marta Searchable']);
        $otherAuthor = User::factory()->create(['name' => 'Other Person']);

        $matchingThread = Thread::factory()->for($matchingAuthor)->create([
            'title' => 'Thread from matching author',
        ]);
        $otherThread = Thread::factory()->for($otherAuthor)->create([
            'title' => 'Thread from non matching author',
        ]);

        $this->actingAs($viewer)
            ->get(route('forum.index', ['search' => 'Marta Searchable']))
            ->assertStatus(200)
            ->assertSee($matchingThread->title)
            ->assertDontSee($otherThread->title);
    }

    #[Test]
    public function thread_reply_search_can_filter_by_reply_body_text()
    {
        $viewer = User::factory()->create();
        $thread = Thread::factory()->create();

        $matchingPost = Post::factory()->for($thread)->create([
            'body' => 'Unique body phrase for matching reply',
        ]);
        $otherPost = Post::factory()->for($thread)->create([
            'body' => 'Different reply body that should not match',
        ]);

        $this->actingAs($viewer)
            ->get(route('threads.show', [
                'slug' => $thread->slug,
                'reply_search' => 'matching reply',
            ]))
            ->assertStatus(200)
            ->assertSee($matchingPost->body)
            ->assertDontSee($otherPost->body);
    }

    #[Test]
    public function thread_reply_search_can_filter_by_reply_author_name()
    {
        $viewer = User::factory()->create();
        $thread = Thread::factory()->create();

        $matchingAuthor = User::factory()->create([
            'name' => 'Reply Search Target',
        ]);
        $otherAuthor = User::factory()->create([
            'name' => 'Reply Search Other',
        ]);

        $matchingPost = Post::factory()->for($thread)->for($matchingAuthor)->create([
            'body' => 'Body from matching author',
        ]);
        $otherPost = Post::factory()->for($thread)->for($otherAuthor)->create([
            'body' => 'Body from other author',
        ]);

        $this->actingAs($viewer)
            ->get(route('threads.show', [
                'slug' => $thread->slug,
                'reply_search' => 'Reply Search Target',
            ]))
            ->assertStatus(200)
            ->assertSee($matchingAuthor->name)
            ->assertSee($matchingPost->body)
            ->assertDontSee($otherPost->body);
    }

    #[Test]
    public function creating_thread_bumps_forum_cache_version()
    {
        Cache::flush();
        Cache::forever('forum_cache_version', 7);

        $user = User::factory()->create();
        $subforum = Subforum::factory()->create();

        $this->actingAs($user)
            ->post(route('threads.store'), [
                'title' => 'Cache bump discussion',
                'body' => 'Body for cache bump test.',
                'subforum_id' => $subforum->id,
            ])
            ->assertRedirect();

        $this->assertSame(8, (int) Cache::get('forum_cache_version'));
    }
}