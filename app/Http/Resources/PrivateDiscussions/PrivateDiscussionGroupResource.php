<?php

namespace App\Http\Resources\PrivateDiscussions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivateDiscussionGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestMessage = $this->messages->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'members' => $this->members->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
            ])->all(),
            'latest_message' => $latestMessage ? [
                'body' => $latestMessage->body,
                'user_name' => $latestMessage->user?->name,
                'created_at' => $latestMessage->created_at,
            ] : null,
            'updated_at' => $this->updated_at,
        ];
    }
}
