<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Subforum::create([
            'name' => 'Basketball',
            'description' => 'Discussions about the NBA, NCAA, and local hoops.',
            'slug' => 'basketball',
        ]);

        Subforum::create([
            'name' => 'Volleyball',
            'description' => 'Everything from beach volleyball to indoor leagues.',
            'slug' => 'volleyball',
        ]);

        Subforum::create([
            'name' => 'Gaming Corner',
            'description' => 'The latest on new releases, hardware, and e-sports.',
            'slug' => 'gaming-corner',
        ]);

        Subforum::create([
            'name' => 'Homework Help',
            'description' => 'Collaborate on school assignments and study tips.',
            'slug' => 'homework-help',
        ]);
    }
}
