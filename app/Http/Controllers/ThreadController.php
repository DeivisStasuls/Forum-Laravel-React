<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Display a listing of the threads, grouped by subforum.
     */
    public function index(Request $request)
    {
        // 1. Fetch all threads and eager load the user (author)
        // Order by latest post/update or simply latest creation for now
        $threads = Thread::with('user')
            ->latest() // Order by creation date descending
            ->get();

        // 2. Define the subforum groups (These should ideally come from a DB table)
        // I'll assume your Thread model has a 'subforum' column for this grouping.
        // For the example, I'll group all existing threads under 'General Discussion'
        // and add some empty conceptual subforums.

        $subforums = [
            'General Discussion' => [
                'description' => 'Discussions that don\'t fit anywhere else.',
                'threads' => collect(), // Initial empty collection
            ],
            'Sports Talk' => [
                'description' => 'Basketball, Volleyball, and other sports news.',
                'threads' => collect(),
            ],
            'Gaming Corner' => [
                'description' => 'Latest news and discussions about games.',
                'threads' => collect(),
            ],
            'Homework Help' => [
                'description' => 'Ask questions and help others with studies.',
                'threads' => collect(),
            ],
        ];

        // 3. Populate the 'General Discussion' subforum with all threads for this initial step
        // In a proper setup, you would filter the threads based on a 'subforum_id' or 'category' column.
        $subforums['General Discussion']['threads'] = $threads;


        // 4. Render the Inertia page and pass the grouped data
        return Inertia::render('Forum/Index', [
            'subforums' => $subforums,
        ]);
    }

    // ... other methods (store, show, edit, update, destroy)
}