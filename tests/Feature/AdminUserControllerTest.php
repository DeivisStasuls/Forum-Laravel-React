<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_user_management_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertSee('Users'); // adjust based on your Inertia page content
    }

    /** @test */
    public function non_admin_cannot_access_user_management_page()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    }

    /** @test */
    public function admin_can_promote_and_demote_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // Promote
        $this->actingAs($admin)
            ->patch(route('admin.users.promote', $user))
            ->assertRedirect();

        $this->assertEquals('admin', $user->fresh()->role);

        // Demote
        $this->actingAs($admin)
            ->patch(route('admin.users.demote', $user))
            ->assertRedirect();

        $this->assertEquals('user', $user->fresh()->role);
    }

    /** @test */
    public function admin_cannot_demote_self()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.demote', $admin))
            ->assertSessionHasErrors('email');

        $this->assertEquals('admin', $admin->fresh()->role);
    }

    /** @test */
    public function admin_can_ban_and_unban_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['banned_at' => null]);

        // Ban
        $this->actingAs($admin)
            ->patch(route('admin.users.ban', $user), [
                'reason' => 'Spam and repeated rule violations.',
            ])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->banned_at);
        $this->assertEquals('Spam and repeated rule violations.', $user->fresh()->ban_reason);

        // Unban
        $this->actingAs($admin)
            ->patch(route('admin.users.unban', $user))
            ->assertRedirect();

        $this->assertNull($user->fresh()->banned_at);
    }

    /** @test */
    public function admin_cannot_ban_self()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.ban', $admin), [
                'reason' => 'Test reason.',
            ])
            ->assertSessionHasErrors('email');

        $this->assertNull($admin->fresh()->banned_at);
    }

    /** @test */
    public function admin_must_provide_reason_when_banning_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['banned_at' => null]);

        $this->actingAs($admin)
            ->patch(route('admin.users.ban', $user), [
                'reason' => '',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertNull($user->fresh()->banned_at);
        $this->assertNull($user->fresh()->ban_reason);
    }
}