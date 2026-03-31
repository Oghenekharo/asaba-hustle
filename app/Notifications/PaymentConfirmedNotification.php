<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PaymentConfirmedNotification extends Notification
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
    //         'title' => 'Payment confirmed',
    //         'body' => "{$this->workerName} confirmed payment for \"{$this->jobTitle}\". Job is now closed.",
    //         'url' => $this->url,
    //         'type' => NotificationType::PAYMENT,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('💰 Payment confirmed')
            ->body("{$this->workerName} confirmed payment. \"{$this->jobTitle}\" is now complete.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::PAYMENT,
            ]);
    }
}
