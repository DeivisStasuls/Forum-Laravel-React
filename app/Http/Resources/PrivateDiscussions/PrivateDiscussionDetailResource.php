<?php

namespace App\Http\Resources\PrivateDiscussions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivateDiscussionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_by' => $this->created_by,
            'can_manage' => $request->user()?->id === $this->created_by,
            'members' => $this->members->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
            ])->all(),
            'messages' => PrivateDiscussionMessageResource::collection($this->messages)->resolve(),
        ];
    }
}
