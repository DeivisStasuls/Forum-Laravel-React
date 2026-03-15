<?php

namespace Database\Seeders;

use App\Models\Subforum;
use Illuminate\Database\Seeder;

class SubforumSeeder extends Seeder
{
    /**
     * Seed school-focused forum categories.
     */
    public function run(): void
    {
        $subforums = [
            [
                'name' => 'Sports',
                'description' => 'Discuss school teams, matches, training, and tryouts.',
                'slug' => 'sports',
                'restricted_thread_creation' => false,
            ],
            [
                'name' => 'Education',
                'description' => 'Homework, study strategies, exams, and academic resources.',
                'slug' => 'education',
                'restricted_thread_creation' => false,
            ],
            [
                'name' => 'School Announcements',
                'description' => 'Official school updates, deadlines, notices, and policy changes.',
                'slug' => 'announcements',
                'restricted_thread_creation' => true,
            ],
            [
                'name' => 'Clubs and Activities',
                'description' => 'School clubs, competitions, volunteer projects, and events.',
                'slug' => 'clubs-and-activities',
                'restricted_thread_creation' => false,
            ],
            [
                'name' => 'Campus Life',
                'description' => 'Cafeteria, transport, schedules, and student life topics.',
                'slug' => 'campus-life',
                'restricted_thread_creation' => false,
            ],
            [
                'name' => 'General Discussion',
                'description' => 'Open conversation for anything related to the school community.',
                'slug' => 'general-discussion',
                'restricted_thread_creation' => false,
            ],
        ];

        foreach ($subforums as $subforum) {
            Subforum::updateOrCreate(
                ['slug' => $subforum['slug']],
                [
                    'name' => $subforum['name'],
                    'description' => $subforum['description'],
                    'restricted_thread_creation' => $subforum['restricted_thread_creation'],
                ]
            );
        }
    }
}
