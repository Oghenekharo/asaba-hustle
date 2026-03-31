<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WorkerAcceptedJobNotification extends Notification
{
    public function __construct(
        public string $workerName,
        public string $jobTitle,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'Worker accepted your job',
    //         'body' => "{$this->workerName} accepted \"{$this->jobTitle}\" and is ready to begin.",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🛠️ Worker accepted your job')
            ->body("{$this->workerName} is ready to begin \"{$this->jobTitle}\". Stay available for updates")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
            ]);
    }
}
