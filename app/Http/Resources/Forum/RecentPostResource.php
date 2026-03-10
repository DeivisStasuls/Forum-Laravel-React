<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class RecentPostResource extends JsonResource
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
            'body' => Str::limit($this->body, 100),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'thread' => [
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'slug' => $this->thread->slug,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
