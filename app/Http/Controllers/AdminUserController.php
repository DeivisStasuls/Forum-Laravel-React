<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminUserResource;
use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use App\Services\AdminUserQueryService;
use App\Services\ForumQueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly AdminUserQueryService $adminUserQueryService,
        private readonly ForumQueryService $forumQueryService
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('manageUsers', User::class);
        $users = $this->adminUserQueryService->getUsersForManagement();
        $subforums = Subforum::query()
            ->with(['moderators:id,name,email'])
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        $assignableUsers = User::query()
            ->whereNull('banned_at')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Users', [
            'users' => AdminUserResource::collection($users)->resolve(),
            'subforums' => $subforums->map(fn (Subforum $subforum) => [
                'id' => $subforum->id,
                'name' => $subforum->name,
                'slug' => $subforum->slug,
                'moderators' => $subforum->moderators->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])->values()->all(),
            ])->values()->all(),
            'assignableUsers' => $assignableUsers->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values()->all(),
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
        $this->authorize('manageUsers', User::class);

        $user->update(['role' => 'admin']);

        return back()->with('success', 'User promoted to admin.');
    }

    public function demote(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot demote yourself.']);
        }

        $user->update(['role' => 'user']);

        return back()->with('success', 'Admin privileges removed.');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot ban yourself.']);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user->update([
            'banned_at' => now(),
            'ban_reason' => $validated['reason'],
        ]);

        return back()->with('success', 'User banned successfully.');
    }

    public function unban(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        $user->update([
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        return back()->with('success', 'User unbanned successfully.');
    }

    public function warn(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot warn yourself.']);
        }

        $user->increment('warnings_count');

        return back()->with('success', 'Warning added successfully.');
    }

    public function removeWarning(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot modify your own warnings.']);
        }

        $currentWarnings = (int) $user->warnings_count;

        if ($currentWarnings <= 0) {
            return back()->withErrors(['email' => 'User has no warnings to remove.']);
        }

        $user->update([
            'warnings_count' => $currentWarnings - 1,
        ]);

        return back()->with('success', 'Warning removed successfully.');
    }

    public function assignModerator(Request $request): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        $validated = $request->validate([
            'subforum_id' => ['required', 'integer', 'exists:subforums,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $subforum = Subforum::query()->findOrFail($validated['subforum_id']);
        $user = User::query()
            ->whereNull('banned_at')
            ->findOrFail($validated['user_id']);

        $subforum->moderators()->syncWithoutDetaching([$user->id]);
        $this->forumQueryService->bumpForumCacheVersion();

        return back()->with('success', 'Moderator assigned successfully.');
    }

    public function removeModerator(Request $request, Subforum $subforum, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', User::class);

        $subforum->moderators()->detach($user->id);
        $this->forumQueryService->bumpForumCacheVersion();

        return back()->with('success', 'Moderator removed successfully.');
    }
}
