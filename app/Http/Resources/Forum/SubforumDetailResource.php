<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubforumDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isModerator = $request->user()?->isModeratorOf($this->resource) ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'is_moderator' => $isModerator,
            'restricted_thread_creation' => (bool) $this->restricted_thread_creation,
            'can_create_threads' => ! $this->restricted_thread_creation
                || $request->user()?->isAdmin()
                || $isModerator,
            'moderators' => $this->moderators->map(fn ($moderator) => [
                'id' => $moderator->id,
                'name' => $moderator->name,
            ])->values()->all(),
            'threads' => SubforumThreadResource::collection($this->threads)->resolve(),
        ];
    }
}
