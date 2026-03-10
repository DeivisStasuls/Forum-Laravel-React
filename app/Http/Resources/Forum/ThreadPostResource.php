<?php

namespace App\Http\Resources\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadPostResource extends JsonResource
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
            'body' => $this->body,
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
