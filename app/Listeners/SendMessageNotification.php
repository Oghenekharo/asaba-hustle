<?php

namespace App\Listeners;

use App\Services\UserNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMessageNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    protected $notificationService;

    public function __construct(UserNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $message = $event->message;
        $conversation = $message->conversation;

        $receiver = $message->sender_id == $conversation->client_id
            ? $conversation->worker_id
            : $conversation->client_id;

        $this->notificationService->create(
            $receiver,
            'New message',
            $message->message,
            'message'
        );
    }
}
