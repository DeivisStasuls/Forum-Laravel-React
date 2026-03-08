<?php

namespace App\Http\Controllers;

use App\Models\PrivateGroup;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PrivateDiscussionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $groups = PrivateGroup::query()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['members:id,name', 'messages' => function ($query) {
                $query->latest()->limit(1)->with('user:id,name');
            }])
            ->latest()
            ->get();

        $users = User::query()
            ->where('id', '!=', $user->id)
            ->whereNull('banned_at')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return Inertia::render('PrivateDiscussions/Index', [
            'groups' => $groups->map(function ($group) {
                $latestMessage = $group->messages->first();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'members' => $group->members->map(fn ($member) => [
                        'id' => $member->id,
                        'name' => $member->name,
                    ]),
                    'latest_message' => $latestMessage ? [
                        'body' => $latestMessage->body,
                        'user_name' => $latestMessage->user?->name,
                        'created_at' => $latestMessage->created_at,
                    ] : null,
                    'updated_at' => $group->updated_at,
                ];
            }),
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $memberIds = collect($data['member_ids'])
            ->push($request->user()->id)
            ->unique()
            ->values();

        $group = PrivateGroup::create([
            'name' => $data['name'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $group->members()->sync($memberIds);

        return Redirect::route('private-discussions.show', $group->id)
            ->with('success', 'Private discussion group created.');
    }

    public function show(Request $request, PrivateGroup $privateGroup): Response
    {
        $this->authorizeMember($request, $privateGroup);

        $privateGroup->load([
            'members:id,name',
            'messages.user:id,name',
        ]);

        $currentUser = $request->user();
        $canManage = $currentUser->id === $privateGroup->created_by;

        $availableUsers = User::query()
            ->where('id', '!=', $currentUser->id)
            ->whereNull('banned_at')
            ->whereNotIn('id', $privateGroup->members->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('PrivateDiscussions/Show', [
            'group' => [
                'id' => $privateGroup->id,
                'name' => $privateGroup->name,
                'created_by' => $privateGroup->created_by,
                'can_manage' => $canManage,
                'members' => $privateGroup->members->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                ]),
                'messages' => $privateGroup->messages->map(fn ($message) => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                    ],
                    'created_at' => $message->created_at,
                ]),
                'available_users' => $availableUsers,
            ],
        ]);
    }

    public function messages(Request $request, PrivateGroup $privateGroup): JsonResponse
    {
        $this->authorizeMember($request, $privateGroup);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $privateGroup->messages()
            ->with('user:id,name')
            ->when($afterId > 0, function ($query) use ($afterId) {
                $query->where('id', '>', $afterId);
            })
            ->orderBy('id')
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
                'created_at' => $message->created_at,
            ]);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeMember($request, $privateGroup);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        PrivateMessage::create([
            'private_group_id' => $privateGroup->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return Redirect::route('private-discussions.show', $privateGroup->id);
    }

    public function update(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeCreator($request, $privateGroup);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $privateGroup->update([
            'name' => $data['name'] ?? null,
        ]);

        return Redirect::route('private-discussions.show', $privateGroup->id)
            ->with('success', 'Group updated successfully.');
    }

    public function addMember(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeCreator($request, $privateGroup);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->whereNull('banned_at')->findOrFail($data['user_id']);
        $privateGroup->members()->syncWithoutDetaching([$user->id]);

        return Redirect::route('private-discussions.show', $privateGroup->id)
            ->with('success', 'Member added successfully.');
    }

    public function removeMember(Request $request, PrivateGroup $privateGroup, User $user): RedirectResponse
    {
        $this->authorizeCreator($request, $privateGroup);

        if ($user->id === $privateGroup->created_by) {
            return back()->withErrors([
                'group' => 'You cannot remove the group creator.',
            ]);
        }

        $privateGroup->members()->detach($user->id);

        return Redirect::route('private-discussions.show', $privateGroup->id)
            ->with('success', 'Member removed successfully.');
    }

    public function leave(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeMember($request, $privateGroup);

        $currentUserId = $request->user()->id;

        DB::transaction(function () use ($privateGroup, $currentUserId) {
            if ($privateGroup->created_by === $currentUserId) {
                $newOwnerId = $privateGroup->members()
                    ->where('users.id', '!=', $currentUserId)
                    ->orderBy('users.id')
                    ->value('users.id');

                if ($newOwnerId) {
                    $privateGroup->update(['created_by' => $newOwnerId]);
                }
            }

            $privateGroup->members()->detach($currentUserId);

            if (! $privateGroup->members()->exists()) {
                $privateGroup->delete();
            }
        });

        return Redirect::route('private-discussions.index')
            ->with('success', 'You left the private discussion.');
    }

    public function destroy(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeCreator($request, $privateGroup);

        $privateGroup->delete();

        return Redirect::route('private-discussions.index')
            ->with('success', 'Private discussion deleted.');
    }

    private function authorizeMember(Request $request, PrivateGroup $privateGroup): void
    {
        if (! $privateGroup->members()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function authorizeCreator(Request $request, PrivateGroup $privateGroup): void
    {
        if ($privateGroup->created_by !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
