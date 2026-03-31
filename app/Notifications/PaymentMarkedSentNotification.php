<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PaymentMarkedSentNotification extends Notification
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
    //         'title' => 'Payment marked as sent',
    //         'body' => "{$this->clientName} marked \"{$this->jobTitle}\" as paid. Please confirm receipt.",
    //         'url' => $this->url,
    //         'type' => NotificationType::PAYMENT,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🤑 Payment marked as sent')
            ->body("{$this->clientName} marked \"{$this->jobTitle}\" as paid. Confirm receipt.")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::PAYMENT,
            ]);
    }
}
