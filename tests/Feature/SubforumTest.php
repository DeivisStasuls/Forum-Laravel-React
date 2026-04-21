<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subforum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SubforumTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_subforum_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_access_create_page()
    {
        $admin = User::factory()->create([
        'role' => 'admin'
        ]);;

        $response = $this->actingAs($admin)
            ->get(route('subforums.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function non_admin_cannot_access_create_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.create'));

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_create_subforum()
    {
        $admin = User::factory()->create([
        'role' => 'admin'
        ]);;

        $response = $this->actingAs($admin)
            ->post(route('subforums.store'), [
                'name' => 'Gaming',
                'description' => 'Gaming discussions'
            ]);

        $this->assertDatabaseHas('subforums', [
            'name' => 'Gaming'
        ]);
    }

    #[Test]
    public function non_admin_cannot_create_subforum()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('subforums.store'), [
                'name' => 'Blocked Category',
                'description' => 'Should fail',
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function user_can_view_subforum()
    {
        $user = User::factory()->create();

        $subforum = Subforum::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.show', $subforum->slug));

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_subforum()
    {
        $admin = User::factory()->create([
        'role' => 'admin'
        ]);

        $subforum = Subforum::factory()->create();

        $response = $this->actingAs($admin)
            ->patch(route('subforums.update', $subforum->slug), [
                'name' => 'Updated Category',
                'description' => 'Updated description'
            ]);

        $this->assertDatabaseHas('subforums', [
            'name' => 'Updated Category'
        ]);
    }

    #[Test]
    public function non_admin_cannot_update_subforum()
    {
        $user = User::factory()->create();
        $subforum = Subforum::factory()->create();

        $this->actingAs($user)
            ->patch(route('subforums.update', $subforum->slug), [
                'name' => 'Unauthorized Update',
                'description' => 'Should fail',
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function admin_can_delete_subforum()
    {
        $admin = User::factory()->create([
        'role' => 'admin'
        ]);;

        $subforum = Subforum::factory()->create();

        $this->actingAs($admin)
            ->delete(route('subforums.destroy', $subforum->slug));

        $this->assertDatabaseMissing('subforums', [
            'id' => $subforum->id
        ]);
    }

    #[Test]
    public function non_admin_cannot_delete_subforum()
    {
        $user = User::factory()->create();
        $subforum = Subforum::factory()->create();

        $this->actingAs($user)
            ->delete(route('subforums.destroy', $subforum->slug))
            ->assertStatus(403);

        $this->assertDatabaseHas('subforums', [
            'id' => $subforum->id,
        ]);
    }
}