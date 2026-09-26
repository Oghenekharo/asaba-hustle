<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SendchampSmsService
{
    public function send(string $phone, string $message): void
    {
        $token = (string) config('services.sendchamp.token');
        $sender = (string) config('services.sendchamp.sender_name');
        $route = (string) config('services.sendchamp.route');
        $baseUrl = rtrim((string) config('services.sendchamp.base_url'), '/');

        if ($token === '' || $sender === '' || $route === '' || $baseUrl === '') {
            throw new RuntimeException('Sendchamp SMS credentials are not configured.');
        }

        $response = Http::withToken($token)
            ->asJson()
            ->acceptJson()
            ->timeout(15)
            ->post($baseUrl . '/sms/send', [
                'to' => [$this->normalizePhone($phone)],
                'message' => $message,
                'sender_name' => $sender,
                'route' => $route,
            ]);

        if (!$response->successful()) {
            $payload = $response->json();
            $reason = is_array($payload)
                ? (string) ($payload['message'] ?? $payload['error'] ?? '')
                : '';

            throw new RuntimeException(sprintf(
                'Sendchamp SMS request failed (HTTP %d)%s.',
                $response->status(),
                $reason !== '' ? ': ' . $reason : ''
            ));
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '234' . substr($digits, 1);
        }

        if (str_starts_with($digits, '234')) {
            return $digits;
        }

        return $digits;
    }
}
