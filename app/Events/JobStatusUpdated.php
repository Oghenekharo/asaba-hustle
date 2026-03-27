<?php

namespace App\Events;

use App\Models\ServiceJob;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceJob $job,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('job.' . $this->job->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'job.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->job->id,
            'status' => $this->job->status,
            'assigned_to' => $this->job->assigned_to,
            'paid_at' => $this->job->paid_at,
            'updated_at' => $this->job->updated_at,
            'has_rating' => $this->job->rating !== null,
        ];
    }
}
