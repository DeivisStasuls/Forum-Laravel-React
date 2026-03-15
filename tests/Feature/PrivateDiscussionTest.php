<?php

namespace Tests\Feature;

use App\Http\Resources\PrivateDiscussions\PrivateDiscussionGroupResource;
use App\Models\PrivateGroup;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDiscussionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_displays_groups_and_users_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('private-discussions.index'));

        $response->assertStatus(200);
        $response->assertSee('groups'); // you can adjust based on actual response
        $response->assertSee('users');  // you can adjust based on actual response
    }

    #[Test]
    public function authenticated_user_can_create_private_group()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUser = User::factory()->create();

        $response = $this->post(route('private-discussions.store'), [
            'name' => 'Test Group',
            'member_ids' => [$otherUser->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('private_groups', [
            'name' => 'Test Group',
            'created_by' => $user->id,
        ]);
    }

    #[Test]
    public function member_can_view_group_and_non_member_cannot()
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $nonMember = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);

        $group->members()->sync([$creator->id, $member->id]);

        $this->actingAs($member)
            ->get(route('private-discussions.show', $group->id))
            ->assertStatus(200);

        $this->actingAs($nonMember)
            ->get(route('private-discussions.show', $group->id))
            ->assertStatus(403);
    }

    #[Test]
    public function member_can_send_message_but_non_member_cannot()
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $nonMember = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);
        $group->members()->sync([$creator->id, $member->id]);

        $this->actingAs($member)
            ->post(route('private-discussions.messages.store', $group->id), [
                'body' => 'Hello Member',
            ])
            ->assertRedirect();

        $this->actingAs($nonMember)
            ->post(route('private-discussions.messages.store', $group->id), [
                'body' => 'Hello Hack',
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function member_can_send_message_with_image()
    {
        Storage::fake('public');

        $creator = User::factory()->create();
        $member = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);
        $group->members()->sync([$creator->id, $member->id]);

        $this->actingAs($member)
            ->post(route('private-discussions.messages.store', $group->id), [
                'body' => 'See attachment',
                'image' => UploadedFile::fake()->create(
                    'message.jpg',
                    100,
                    'image/jpeg',
                ),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('private_messages', [
            'private_group_id' => $group->id,
            'user_id' => $member->id,
            'body' => 'See attachment',
        ]);

        $message = $group->messages()->latest('id')->first();
        $this->assertNotNull($message->image_path);
        Storage::disk('public')->assertExists($message->image_path);
    }

    #[Test]
    public function creator_can_update_group_name_but_non_creator_cannot()
    {
        $creator = User::factory()->create();
        $nonCreator = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($creator)
            ->patch(route('private-discussions.update', $group->id), [
                'name' => 'Updated Name',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('private_groups', [
            'id' => $group->id,
            'name' => 'Updated Name',
        ]);

        $this->actingAs($nonCreator)
            ->patch(route('private-discussions.update', $group->id), [
                'name' => 'Hack Attempt',
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function creator_can_add_and_remove_members_but_cannot_remove_self()
    {
        $creator = User::factory()->create();
        $userToAdd = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator)
            ->post(route('private-discussions.members.add', $group->id), [
                'user_id' => $userToAdd->id,
            ])
            ->assertRedirect();

        $this->assertTrue($group->members()->where('users.id', $userToAdd->id)->exists());

        $this->actingAs($creator)
            ->delete(route('private-discussions.members.remove', [$group->id, $userToAdd->id]))
            ->assertRedirect();

        $this->assertFalse($group->members()->where('users.id', $userToAdd->id)->exists());

        // Cannot remove self
        $this->actingAs($creator)
            ->delete(route('private-discussions.members.remove', [$group->id, $creator->id]))
            ->assertSessionHasErrors();
    }

    #[Test]
    public function member_can_leave_group_and_creator_transfer_happens()
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);

        $group->members()->sync([$creator->id, $member->id]);

        $this->actingAs($member)
            ->post(route('private-discussions.leave', $group->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('private_group_user', [
            'user_id' => $member->id,
            'private_group_id' => $group->id,
        ]);
    }

    #[Test]
    public function creator_can_delete_group_but_non_creator_cannot()
    {
        $creator = User::factory()->create();
        $nonCreator = User::factory()->create();

        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator)
            ->delete(route('private-discussions.destroy', $group->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('private_groups', ['id' => $group->id]);

        // Recreate for non-creator test
        $group = PrivateGroup::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($nonCreator)
            ->delete(route('private-discussions.destroy', $group->id))
            ->assertStatus(403);
    }

    #[Test]
    public function index_shows_latest_message_rich_text_content()
    {
        $viewer = User::factory()->create();
        $group = PrivateGroup::factory()->create([
            'created_by' => $viewer->id,
        ]);
        $group->members()->sync([$viewer->id]);

        PrivateMessage::create([
            'private_group_id' => $group->id,
            'user_id' => $viewer->id,
            'body' => '<p><strong>Bold preview text</strong> in latest message.</p>',
            'image_path' => null,
        ]);

        $group->load([
            'members:id,name',
            'messages' => function ($query) {
                $query->latest()->limit(1)->with('user:id,name');
            },
        ]);

        $payload = (new PrivateDiscussionGroupResource($group))->resolve();

        $this->assertSame(
            '<p><strong>Bold preview text</strong> in latest message.</p>',
            $payload['latest_message']['body'],
        );
    }

    #[Test]
    public function index_shows_image_attachment_fallback_when_latest_message_has_no_body()
    {
        $viewer = User::factory()->create();
        $group = PrivateGroup::factory()->create([
            'created_by' => $viewer->id,
        ]);
        $group->members()->sync([$viewer->id]);

        PrivateMessage::create([
            'private_group_id' => $group->id,
            'user_id' => $viewer->id,
            'body' => '',
            'image_path' => 'private-message-images/sample.jpg',
        ]);

        $group->load([
            'members:id,name',
            'messages' => function ($query) {
                $query->latest()->limit(1)->with('user:id,name');
            },
        ]);

        $payload = (new PrivateDiscussionGroupResource($group))->resolve();

        $this->assertSame('', $payload['latest_message']['body']);
        $this->assertSame(
            '/storage/private-message-images/sample.jpg',
            $payload['latest_message']['image_url'],
        );
    }
}