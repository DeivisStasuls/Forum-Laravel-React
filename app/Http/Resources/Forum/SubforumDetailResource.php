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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'threads' => SubforumThreadResource::collection($this->threads)->resolve(),
        ];
    }
}
