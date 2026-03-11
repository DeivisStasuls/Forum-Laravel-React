<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadDetailResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'slug' => $this->slug,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'edited_by' => $this->editor ? [
                'id' => $this->editor->id,
                'name' => $this->editor->name,
                'role' => $this->editor->role,
            ] : null,
            'edited_at' => $this->edited_at,
            'subforum' => [
                'id' => $this->subforum->id,
                'name' => $this->subforum->name,
                'slug' => $this->subforum->slug,
            ],
            'posts_count' => $this->posts_count,
            'score' => $this->score,
            'user_vote' => $this->user_vote,
            'creator_only_comments' => $this->creator_only_comments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'posts' => ThreadPostResource::collection($this->posts)->resolve(),
        ];
    }
}
