<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subforum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubforumTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_subforum_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_create_page()
    {
        $admin = User::factory()->create([
        'role' => 'admin'
        ]);;

        $response = $this->actingAs($admin)
            ->get(route('subforums.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_create_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.create'));

        $response->assertStatus(403);
    }

    /** @test */
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

    /** @test */
    public function user_can_view_subforum()
    {
        $user = User::factory()->create();

        $subforum = Subforum::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('subforums.show', $subforum->slug));

        $response->assertStatus(200);
    }

    /** @test */
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

    /** @test */
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
}