<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PrivateGroup;
use App\Models\PrivateMessage;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultUser = User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => 'password',
            'role' => 'user',
            'email_verified_at' => now(),
            'warnings_count' => 0,
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        $defaultAdmin = User::updateOrCreate([
            'email' => 'admin@forum.local',
        ], [
            'name' => 'Default Admin',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
            'warnings_count' => 0,
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        $this->call(SubforumSeeder::class);

        $extraAdmins = User::factory(2)->create([
            'role' => 'admin',
            'warnings_count' => 0,
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        $members = User::factory(24)->create([
            'role' => 'user',
        ]);

        $warnedUsers = $members->take(4);
        foreach ($warnedUsers as $warnedUser) {
            $warnedUser->update([
                'warnings_count' => fake()->numberBetween(1, 3),
            ]);
        }

        $bannedUser = $members->last();
        if ($bannedUser) {
            $bannedUser->update([
                'banned_at' => now()->subDays(2),
                'ban_reason' => 'Repeated spam in multiple categories.',
            ]);
        }

        $activeUsers = User::query()
            ->whereNull('banned_at')
            ->get();
        $subforums = Subforum::query()->get();

        $this->seedSubforumModerators($subforums, $activeUsers);
        $this->seedThreadsAndPosts($subforums, $activeUsers);
        $this->seedPrivateGroups($activeUsers, collect([$defaultAdmin, $defaultUser])->merge($extraAdmins));
    }

    private function seedSubforumModerators(EloquentCollection $subforums, EloquentCollection $activeUsers): void
    {
        $moderatorPool = $activeUsers
            ->where('role', 'user')
            ->shuffle()
            ->values();

        foreach ($subforums as $index => $subforum) {
            $offset = $index * 2;
            $selected = $moderatorPool->slice($offset, 2)->pluck('id')->all();

            if (! empty($selected)) {
                $subforum->moderators()->syncWithoutDetaching($selected);
            }
        }
    }

    private function seedThreadsAndPosts(EloquentCollection $subforums, EloquentCollection $activeUsers): void
    {
        foreach ($subforums as $subforum) {
            $eligibleUsers = $subforum->restricted_thread_creation
                ? $activeUsers
                    ->filter(fn (User $user) => $user->isAdmin() || $subforum->moderators->contains('id', $user->id))
                    ->values()
                : $activeUsers->values();

            if ($eligibleUsers->isEmpty()) {
                continue;
            }

            $threadCount = fake()->numberBetween(5, 10);
            for ($i = 0; $i < $threadCount; $i++) {
                $author = $eligibleUsers->random();
                $thread = Thread::factory()->create([
                    'user_id' => $author->id,
                    'subforum_id' => $subforum->id,
                ]);
                $this->seedVotesForVotable($thread, $activeUsers, 18, $author->id);

                $postCount = fake()->numberBetween(2, 8);
                for ($j = 0; $j < $postCount; $j++) {
                    $post = Post::factory()->create([
                        'thread_id' => $thread->id,
                        'user_id' => $activeUsers->random()->id,
                    ]);
                    $this->seedVotesForVotable(
                        $post,
                        $activeUsers,
                        12,
                        (int) $post->user_id,
                    );
                }
            }
        }
    }

    private function seedPrivateGroups(EloquentCollection $activeUsers, Collection $priorityUsers): void
    {
        $candidates = $activeUsers->shuffle()->values();
        if ($candidates->count() < 4) {
            return;
        }

        for ($i = 0; $i < 4; $i++) {
            $owner = $priorityUsers->filter()->random();
            $members = $candidates->shuffle()->take(4)->pluck('id');
            $memberIds = $members->push($owner->id)->unique()->values();

            $group = PrivateGroup::factory()->create([
                'created_by' => $owner->id,
            ]);

            $group->members()->sync($memberIds->all());

            $messageCount = fake()->numberBetween(3, 10);
            for ($m = 0; $m < $messageCount; $m++) {
                PrivateMessage::create([
                    'private_group_id' => $group->id,
                    'user_id' => $memberIds->random(),
                    'body' => fake()->sentence(fake()->numberBetween(8, 16)),
                ]);
            }
        }
    }

    private function seedVotesForVotable(
        Model $votable,
        EloquentCollection $activeUsers,
        int $maxVotes,
        ?int $excludeUserId = null,
    ): void {
        $votersPool = $activeUsers
            ->when(
                $excludeUserId !== null,
                fn (EloquentCollection $users) => $users->where('id', '!=', $excludeUserId),
            )
            ->values();

        if ($votersPool->isEmpty()) {
            return;
        }

        $voteCount = fake()->numberBetween(0, min($maxVotes, $votersPool->count()));
        $voters = $votersPool->shuffle()->take($voteCount);

        foreach ($voters as $voter) {
            Vote::create([
                'user_id' => $voter->id,
                'votable_id' => $votable->id,
                'votable_type' => $votable::class,
                'value' => fake()->boolean(78) ? 1 : -1,
            ]);
        }
    }
}
