<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'bio' => $this->bio,
            'rating' => $this->rating,
            'is_verified' => $this->is_verified,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'profile_photo' => $this->profile_photo,
            'profile_photo_url' => $this->profile_photo ? Storage::url($this->profile_photo) : null,
            'skill' => new SkillResource($this->whenLoaded('skill')),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'skill_ids' => $this->whenLoaded('skills', fn () => $this->skills->pluck('id')->values()),
            'roles' => $this->getRoleNames(),
            'availability_status' => $this->availability_status,
            'average_rating' => $this->average_rating,
            'created_at' => $this->created_at
        ];
    }
}
