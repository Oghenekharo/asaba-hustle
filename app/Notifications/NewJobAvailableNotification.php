<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewJobAvailableNotification extends Notification
{
    public function __construct(
        public string $jobTitle,
        public string $location,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'New job available',
    //         'body' => "{$this->jobTitle} is available near you.",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🛠️ New job available')
            ->body("{$this->jobTitle} is available near you")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
            ]);
    }
}
