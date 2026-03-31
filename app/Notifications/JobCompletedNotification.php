<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class JobCompletedNotification extends Notification
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
    //         'title' => 'Job marked completed',
    //         'body' => "{$this->workerName} completed \"{$this->jobTitle}\". Confirm the job is completed and proceed to payment.",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🛠️ Job completed')
            ->body("{$this->workerName} marked \"{$this->jobTitle}\" as completed. Confirm the job is completed and proceed to payment.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
            ]);
    }
}
