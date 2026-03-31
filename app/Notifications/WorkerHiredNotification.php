<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WorkerHiredNotification extends Notification
{
    public function __construct(
        public string $jobTitle,
        public string $clientName,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'You were hired!',
    //         'body' => "{$this->clientName} hired you for \"{$this->jobTitle}\"",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🛠️ You were hired!')
            ->body("{$this->clientName} hired you for \"{$this->jobTitle}\". Get ready to start.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
            ]);
    }
}
