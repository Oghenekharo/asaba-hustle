<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class JobStartedNotification extends Notification
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
    //         'title' => 'Job is now in progress',
    //         'body' => "{$this->workerName} started work on \"{$this->jobTitle}\". You’ll be notified when it’s completed.",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🛠️ Job in progress')
            ->body("{$this->workerName} started working on \"{$this->jobTitle}\". You’ll be notified when it’s completed.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
            ]);
    }
}
