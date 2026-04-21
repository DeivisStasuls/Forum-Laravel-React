<?php

namespace App\Http\Controllers;

use App\Http\Resources\PrivateDiscussions\PrivateDiscussionDetailResource;
use App\Http\Resources\PrivateDiscussions\PrivateDiscussionGroupResource;
use App\Http\Resources\PrivateDiscussions\PrivateDiscussionMessageResource;
use App\Http\Resources\UserSummaryResource;
use App\Models\PrivateGroup;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Services\MediaStorageService;
use App\Services\PrivateDiscussionQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PrivateDiscussionController extends Controller
{
    public function __construct(
        private readonly PrivateDiscussionQueryService $privateDiscussionQueryService,
        private readonly MediaStorageService $mediaStorageService
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $groups = $this->privateDiscussionQueryService->getGroupsForUser($user);
        $users = $this->privateDiscussionQueryService->getAvailableUsersForIndex($user);

        return Inertia::render('PrivateDiscussions/Index', [
            'groups' => PrivateDiscussionGroupResource::collection($groups)->resolve(),
            'users' => UserSummaryResource::collection($users)->resolve(),
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

        $availableUsers = $this->privateDiscussionQueryService
            ->getAvailableUsersForGroup($request->user(), $privateGroup);
        $group = (new PrivateDiscussionDetailResource($privateGroup))->resolve();
        $group['available_users'] = UserSummaryResource::collection($availableUsers)->resolve();

        return Inertia::render('PrivateDiscussions/Show', [
            'group' => $group,
        ]);
    }

    public function messages(Request $request, PrivateGroup $privateGroup): JsonResponse
    {
        $this->authorizeMember($request, $privateGroup);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $this->privateDiscussionQueryService->getMessagesForGroup($privateGroup, $afterId);

        return response()->json([
            'messages' => PrivateDiscussionMessageResource::collection($messages)->resolve(),
        ]);
    }

    public function storeMessage(Request $request, PrivateGroup $privateGroup): RedirectResponse
    {
        $this->authorizeMember($request, $privateGroup);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000', 'required_without:image'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        PrivateMessage::create([
            'private_group_id' => $privateGroup->id,
            'user_id' => $request->user()->id,
            'body' => Str::of((string) ($data['body'] ?? ''))->trim()->value(),
            'image_path' => $this->mediaStorageService->storeFromRequest(
                $request,
                'image',
                'private-message-images'
            ),
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
