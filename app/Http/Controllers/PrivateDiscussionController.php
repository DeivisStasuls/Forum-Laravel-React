<?php

namespace App\Http\Controllers;

use App\Models\PrivateGroup;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

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

        return Inertia::render('PrivateDiscussions/Show', [
            'group' => [
                'id' => $privateGroup->id,
                'name' => $privateGroup->name,
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
            ],
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

    private function authorizeMember(Request $request, PrivateGroup $privateGroup): void
    {
        if (! $privateGroup->members()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
