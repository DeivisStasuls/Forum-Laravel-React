<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_via_api_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Api User',
            'email' => 'api-user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'device_name' => 'test-device',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'api-user@example.com');

        $user = User::query()->where('email', 'api-user@example.com')->firstOrFail();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-device',
        ]);
    }

    #[Test]
    public function user_can_login_via_api_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api-login@example.com',
            'password' => 'Password123!',
            'banned_at' => null,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'device_name' => 'ios-phone',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email);
    }

    #[Test]
    public function banned_user_cannot_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'api-banned@example.com',
            'password' => 'Password123!',
            'banned_at' => now(),
            'ban_reason' => 'Spam',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'device_name' => 'blocked-device',
        ])
            ->assertForbidden();
    }

    #[Test]
    public function authenticated_user_can_fetch_current_user_via_api(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-user-check')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    #[Test]
    public function authenticated_user_can_logout_and_revoke_current_token(): void
    {
        $user = User::factory()->create();
        $newToken = $user->createToken('api-logout');
        $tokenId = $newToken->accessToken->id;

        $this->withHeader('Authorization', "Bearer {$newToken->plainTextToken}")
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }
}
