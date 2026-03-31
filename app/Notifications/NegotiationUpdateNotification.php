<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NegotiationUpdateNotification extends Notification
{
    public function __construct(
        public string $type,
        public string $jobTitle,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body())
            ->data([
                'url' => $this->url,
                'type' => 'job',
            ]);
    }

    protected function title(): string
    {
        return match ($this->type) {
            'new_offer' => '🛠️ New offer received',
            'counter_offer' => '🔁 Counter offer received',
            'accepted' => '✅ Offer accepted',
            'rejected' => '⚠️ Offer rejected',
            default => '🔔 Negotiation update',
        };
    }

    protected function body(): string
    {
        return match ($this->type) {
            'new_offer' => "A new offer was made for \"{$this->jobTitle}\"",
            'counter_offer' => "You received a counter offer for \"{$this->jobTitle}\"",
            'accepted' => "Your offer was accepted for \"{$this->jobTitle}\"",
            'rejected' => "Your offer was rejected for \"{$this->jobTitle}\"",
            default => "Negotiation update for \"{$this->jobTitle}\"",
        };
    }
}
