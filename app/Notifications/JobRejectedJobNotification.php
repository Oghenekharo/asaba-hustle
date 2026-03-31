<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WorkerRejectedJobNotification extends Notification
{
    public function __construct(
        public string $jobTitle,
        public string $workerName,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'Worker declined the job',
    //         'body' => "{$this->workerName} declined \"{$this->jobTitle}\". You can assign another worker or renegotiate.",
    //         'url' => $this->url,
    //         'type' => NotificationType::JOB,
    //         'status' => 'rejected',
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('⚠️ Worker declined the job')
            ->body("{$this->workerName} declined \"{$this->jobTitle}\". You can assign another worker or reopen negotiation.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::JOB,
                'status' => 'rejected',
            ]);
    }
}
