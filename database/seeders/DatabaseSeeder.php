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

            $threadSeeds = $this->threadSeedsBySubforum($subforum->slug);
            $replySeeds = $this->replySeedsBySubforum($subforum->slug);
            $threadCount = min(count($threadSeeds), fake()->numberBetween(5, 10));

            for ($i = 0; $i < $threadCount; $i++) {
                $author = $eligibleUsers->random();
                $threadSeed = $threadSeeds[$i % count($threadSeeds)];
                $thread = Thread::factory()->create([
                    'user_id' => $author->id,
                    'subforum_id' => $subforum->id,
                    'title' => $threadSeed['title'],
                    'body' => $threadSeed['body'],
                ]);
                $this->seedVotesForVotable($thread, $activeUsers, 18, $author->id);

                $postCount = fake()->numberBetween(2, 8);
                for ($j = 0; $j < $postCount; $j++) {
                    $replyBody = $replySeeds[$j % count($replySeeds)];
                    $post = Post::factory()->create([
                        'thread_id' => $thread->id,
                        'user_id' => $activeUsers->random()->id,
                        'body' => $replyBody,
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
            $messageSeeds = $this->privateMessageSeeds();
            for ($m = 0; $m < $messageCount; $m++) {
                PrivateMessage::create([
                    'private_group_id' => $group->id,
                    'user_id' => $memberIds->random(),
                    'body' => $messageSeeds[$m % count($messageSeeds)],
                ]);
            }
        }
    }

    private function threadSeedsBySubforum(string $slug): array
    {
        $seedMap = [
            'sports' => [
                [
                    'title' => 'Football team tryout times for next week',
                    'body' => "<p>Coach posted the updated tryout schedule for next week.</p><ul><li>Monday 16:00 - fitness test</li><li>Wednesday 16:00 - small-sided matches</li><li>Friday 15:30 - final selection</li></ul><p>If you need a late arrival due to class, let the coach know in advance.</p>",
                ],
                [
                    'title' => 'Basketball practice plan before district tournament',
                    'body' => '<p>We have two weeks before the district tournament. Can we align on who joins optional morning shooting sessions?</p><p>I can attend Tuesday and Thursday at 7:30.</p>',
                ],
                [
                    'title' => 'Need volunteers for home match organization',
                    'body' => '<p>The school gym hosts a home match on Friday. We need volunteers for scoreboard, seating, and warm-up setup.</p><p>Please comment what role you can take.</p>',
                ],
            ],
            'education' => [
                [
                    'title' => 'Math exam prep materials shared by class 11B',
                    'body' => '<p>We collected the most useful practice tasks for the upcoming math exam.</p><ul><li>Functions and graph tasks</li><li>Probability basics</li><li>Two full mock tests with answers</li></ul><p>Happy to share in this thread.</p>',
                ],
                [
                    'title' => 'Study group for programming assignment 3',
                    'body' => '<p>Anyone interested in a study group for Assignment 3 this Wednesday after classes?</p><p>Main focus: debugging and code structure.</p>',
                ],
                [
                    'title' => 'Tips for organizing weekly homework load',
                    'body' => '<p>I started using a simple weekly checklist and it helped a lot with deadlines.</p><p>If useful, I can post the template here.</p>',
                ],
            ],
            'announcements' => [
                [
                    'title' => 'Updated exam week timetable (official)',
                    'body' => '<p><strong>Official notice:</strong> exam week timetable has been updated by administration.</p><p>Please check classroom changes for Thursday and Friday in the school portal.</p>',
                ],
                [
                    'title' => 'Library opening hours extended during finals',
                    'body' => '<p><strong>Official notice:</strong> library now stays open until 19:00 from Monday to Thursday for finals week.</p>',
                ],
                [
                    'title' => 'Campus access cards revalidation deadline',
                    'body' => '<p><strong>Official notice:</strong> access cards must be revalidated by the end of this month.</p><p>Service desk hours: 08:30-15:30.</p>',
                ],
            ],
            'clubs-and-activities' => [
                [
                    'title' => 'Robotics club recruiting for spring competition',
                    'body' => '<p>Robotics club is recruiting new members for the spring competition season.</p><p>No advanced experience required. Interest and consistency are enough.</p>',
                ],
                [
                    'title' => 'Debate club topic voting for next session',
                    'body' => '<p>Vote for next debate session topic:</p><ul><li>Should AI tools be allowed in exams?</li><li>School uniforms and student choice</li><li>Remote classes in winter</li></ul>',
                ],
                [
                    'title' => 'Volunteer team for open day event',
                    'body' => '<p>Open day is next month and we need volunteers for campus tours, welcome desk, and technical setup.</p>',
                ],
            ],
            'campus-life' => [
                [
                    'title' => 'Bus route delays after 17:00 classes',
                    'body' => '<p>Has anyone found the most reliable route after 17:00 classes?</p><p>Route 4 has been delayed almost every day this week.</p>',
                ],
                [
                    'title' => 'Cafeteria menu feedback for next month',
                    'body' => '<p>Cafeteria asked for student feedback before finalizing next month menu.</p><p>What should stay, and what should be replaced?</p>',
                ],
                [
                    'title' => 'Quiet study spots on campus',
                    'body' => '<p>Sharing a list of quiet spots on campus for focused study between lessons.</p><ul><li>Library second floor corner</li><li>Building B reading room</li><li>Computer lab after 15:30</li></ul>',
                ],
            ],
            'general-discussion' => [
                [
                    'title' => 'Ideas for improving school community events',
                    'body' => '<p>What kind of events would increase participation this semester?</p><p>Workshops, tournaments, or themed evenings?</p>',
                ],
                [
                    'title' => 'Best productivity apps students actually use',
                    'body' => '<p>Looking for practical app suggestions that help with schedules and homework tracking.</p>',
                ],
                [
                    'title' => 'Should we have a monthly student Q&A with staff?',
                    'body' => '<p>Would a monthly Q&amp;A session with teachers and staff be useful?</p><p>If yes, what format should it follow?</p>',
                ],
            ],
        ];

        return $seedMap[$slug] ?? $seedMap['general-discussion'];
    }

    private function replySeedsBySubforum(string $slug): array
    {
        $commonReplies = [
            'Thanks for posting this. The details are clear and easy to follow.',
            'I can help with this and join after the last class period.',
            'Could we pin this thread so everyone sees it this week?',
            'This is useful. Please share an update once timings are confirmed.',
            'I checked with classmates and most of them are available on Wednesday.',
        ];

        $bySubforum = [
            'announcements' => [
                'Confirmed, this update matches what we received in class.',
                'Thanks for the official notice. I will share this with my group.',
                'Can administration also post this in the main hallway board?',
            ],
            'sports' => [
                'I can join the Tuesday training, but not Friday.',
                'Please include warm-up drills before match simulation.',
                'Great plan. Can we also track attendance this week?',
            ],
            'education' => [
                'The mock test section was especially helpful, thank you.',
                'I can share my notes from last year if needed.',
                'Can we add one evening online session for questions?',
            ],
        ];

        return array_merge($bySubforum[$slug] ?? [], $commonReplies);
    }

    private function privateMessageSeeds(): array
    {
        return [
            'I uploaded the draft in Drive. Please review section 2 before tomorrow.',
            'Can we move our meeting to 16:30? I have lab until 16:00.',
            'Looks good. I will finalize slides and send the updated version tonight.',
            'Reminder: presentation rehearsal in room B-204 at 14:15.',
            'I fixed the formatting issue in the document. Please check now.',
            'Let us split tasks: I handle intro, you handle demo and summary.',
        ];
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
