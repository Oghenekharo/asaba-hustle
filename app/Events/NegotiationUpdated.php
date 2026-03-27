<?php

namespace App\Events;

use App\Models\JobNegotiation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NegotiationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $negotiation;

    public function __construct(JobNegotiation $negotiation)
    {
        $this->negotiation = $negotiation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('job.' . $this->negotiation->job_id);
    }

    public function broadcastAs()
    {
        return 'negotiation.updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->negotiation->id,
            'amount' => $this->negotiation->amount,
            'status' => $this->negotiation->status,
            'created_by' => $this->negotiation->created_by,
        ];
    }
}
