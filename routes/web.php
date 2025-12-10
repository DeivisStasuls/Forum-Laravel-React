<?php

use App\Http\Controllers\ThreadController;
use App\Http\Controllers\SubforumController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Forum Routes - All require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    // Forum Index
    Route::get('/forum', [ThreadController::class, 'index'])->name('forum.index');
    
    // Subforum Routes
    Route::get('/subforums', [SubforumController::class, 'index'])->name('subforums.index');
    Route::get('/subforums/{slug}', [SubforumController::class, 'show'])->name('subforums.show');
    
    // Admin-only subforum management routes
    // Note: Authorization is also checked in the controller
    Route::get('/subforums/create', [SubforumController::class, 'create'])->name('subforums.create');
    Route::post('/subforums', [SubforumController::class, 'store'])->name('subforums.store');
    Route::get('/subforums/{slug}/edit', [SubforumController::class, 'edit'])->name('subforums.edit');
    Route::patch('/subforums/{slug}', [SubforumController::class, 'update'])->name('subforums.update');
    Route::delete('/subforums/{slug}', [SubforumController::class, 'destroy'])->name('subforums.destroy');
    
    // Thread Routes
    Route::get('/threads/create', [ThreadController::class, 'create'])->name('threads.create');
    Route::post('/threads', [ThreadController::class, 'store'])->name('threads.store');
    Route::get('/threads/{slug}', [ThreadController::class, 'show'])->name('threads.show');
    Route::get('/threads/{slug}/edit', [ThreadController::class, 'edit'])->name('threads.edit');
    Route::patch('/threads/{slug}', [ThreadController::class, 'update'])->name('threads.update');
    Route::delete('/threads/{slug}', [ThreadController::class, 'destroy'])->name('threads.destroy');
    
    // Post (Reply) Routes
    Route::post('/threads/{threadSlug}/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/threads/{threadSlug}/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/threads/{threadSlug}/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/threads/{threadSlug}/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});
    
require __DIR__.'/auth.php';
