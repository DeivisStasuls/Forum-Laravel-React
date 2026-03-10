<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubforumResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'threads_count' => $this->threads_count,
        ];
    }
}
