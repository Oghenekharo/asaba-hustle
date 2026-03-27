<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'job' => new ServiceJobResource($this->whenLoaded('job')),
            'client_id' => $this->client_id,
            'worker_id' => $this->worker_id,
            'unread_messages_count' => $this->whenCounted('messages', $this->unread_messages_count),
            'latest_message' => $this->whenLoaded('messages', function () {
                return $this->messages->isNotEmpty()
                    ? new ChatMessageResource($this->messages->first())
                    : null;
            }),
            'created_at' => $this->created_at,
        ];
    }
}
