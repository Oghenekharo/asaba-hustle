<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
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
            'conversation_id' => $this->conversation_id,
            'conversation_uuid' => $this->whenLoaded('conversation', fn () => $this->conversation?->uuid),
            'message' => $this->message,
            'is_read' => $this->is_read,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'created_at' => $this->created_at
        ];
    }
}
