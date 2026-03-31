<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ChatMessageNotification extends Notification
{
    public function __construct(
        public string $message,
        public string $url,
        public string $senderName
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'New message',
    //         'body' => $this->message,
    //         'url' => $this->url,
    //         'type' => NotificationType::CHAT,
    //         'sender' => $this->senderName,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('💬 New message from ' . $this->senderName)
            ->body($this->message)
            ->data([
                'url' => $this->url,
                'type' => NotificationType::CHAT,
            ]);
    }
}
