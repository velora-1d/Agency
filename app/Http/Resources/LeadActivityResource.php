<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->diffForHumans(),
            'timestamp' => $this->created_at?->toIso8601String(),
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,
            'comments' => $this->comments
                ->map(fn ($comment): array => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at?->diffForHumans(),
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ] : null,
                ])
                ->values()
                ->all(),
        ];
    }
}
