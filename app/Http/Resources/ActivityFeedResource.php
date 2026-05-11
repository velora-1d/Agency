<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityFeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name),
            ],
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'comments' => ActivityCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at->diffForHumans(),
            'timestamp' => $this->created_at->toIso8601String(),
        ];
    }
}
