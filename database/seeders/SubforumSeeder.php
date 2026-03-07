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
            ],
            [
                'name' => 'Education',
                'description' => 'Homework, study strategies, exams, and academic resources.',
                'slug' => 'education',
            ],
            [
                'name' => 'Announcements',
                'description' => 'Important school updates, deadlines, and notices.',
                'slug' => 'announcements',
            ],
            [
                'name' => 'Clubs and Activities',
                'description' => 'School clubs, competitions, volunteer projects, and events.',
                'slug' => 'clubs-and-activities',
            ],
            [
                'name' => 'Campus Life',
                'description' => 'Cafeteria, transport, schedules, and student life topics.',
                'slug' => 'campus-life',
            ],
            [
                'name' => 'General Discussion',
                'description' => 'Open conversation for anything related to the school community.',
                'slug' => 'general-discussion',
            ],
        ];

        foreach ($subforums as $subforum) {
            Subforum::updateOrCreate(
                ['slug' => $subforum['slug']],
                [
                    'name' => $subforum['name'],
                    'description' => $subforum['description'],
                ]
            );
        }
    }
}
