<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminUserResource;
use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use App\Services\AdminUserQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminUserQueryService $adminUserQueryService
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);
        $users = $this->adminUserQueryService->getUsersForManagement();

        return Inertia::render('Admin/Users', [
            'users' => AdminUserResource::collection($users)->resolve(),
            'forumStats' => [
                'total_threads' => Thread::count(),
                'total_posts' => Post::count(),
                'total_subforums' => Subforum::count(),
                'total_users' => User::count(),
            ],
        ]);
    }

    public function promote(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $user->update(['role' => 'admin']);

        return back()->with('success', 'User promoted to admin.');
    }

    public function demote(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot demote yourself.']);
        }

        $user->update(['role' => 'user']);

        return back()->with('success', 'Admin privileges removed.');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot ban yourself.']);
        }

        $user->update(['banned_at' => now()]);

        return back()->with('success', 'User banned successfully.');
    }

    public function unban(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $user->update(['banned_at' => null]);

        return back()->with('success', 'User unbanned successfully.');
    }

    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
