<?php

namespace App\Support;

class NotificationType
{
    public const JOB = 'job';
    public const CHAT = 'chat';
    public const PAYMENT = 'payment';
    public const RATING = 'rating';
    public const SYSTEM = 'system';

    public static function meta($type)
    {
        return match ($type) {
            self::JOB => [
                'icon' => '🛠️',
                'color' => 'blue',
            ],
            self::CHAT => [
                'icon' => '💬',
                'color' => 'purple',
            ],
            self::PAYMENT => [
                'icon' => '💰',
                'color' => 'green',
            ],
            self::RATING => [
                'icon' => '⭐',
                'color' => 'yellow',
            ],
            default => [
                'icon' => '🔔',
                'color' => 'gray',
            ],
        };
    }
}
