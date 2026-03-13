<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\Subforum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_threads()
{
    $user = User::factory()->create();
    $thread = Thread::factory()->create();

    $this->actingAs($user)
         ->get(route('threads.show', $thread->slug))
         ->assertStatus(200)
         ->assertSee($thread->title);
}

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
}