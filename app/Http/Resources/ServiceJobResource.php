<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceJobResource extends JsonResource
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
            'description' => $this->description,
            'budget' => $this->budget,
            'agreed_amount' => $this->agreed_amount,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
            'payment_status' => $this->status === 'payment_pending'
                ? ($this->paid_at === null
                    ? Payment::STATUS_AWAITING_CONFIRMATION
                    : ($this->payment?->status ?? Payment::STATUS_PENDING))
                : $this->payment?->status,

            'client' => new UserResource($this->whenLoaded('client')),
            'worker' => new UserResource($this->whenLoaded('worker')),
            'skill' => new SkillResource($this->whenLoaded('skill')),
            'rating' => new RatingResource($this->whenLoaded('rating')),
            'worker_rating' => new RatingResource($this->whenLoaded('workerRating')),

            'created_at' => $this->created_at
        ];
    }
}
