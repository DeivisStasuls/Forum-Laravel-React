<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentThreadResource extends JsonResource
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
            'image_url' => $this->image_url,
            'slug' => $this->slug,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'subforum' => [
                'id' => $this->subforum->id,
                'name' => $this->subforum->name,
                'slug' => $this->subforum->slug,
            ],
            'posts_count' => $this->posts_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
