<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NigeriaBulkSmsService
{
    public function send(string $phone, string $message): void
    {
        $username = (string) config('services.nigeriabulksms.username');
        $password = (string) config('services.nigeriabulksms.password');
        $sender = (string) config('services.nigeriabulksms.sender');
        $baseUrl = (string) config('services.nigeriabulksms.base_url');

        if ($username === '' || $password === '' || $sender === '' || $baseUrl === '') {
            throw new RuntimeException('Nigeria Bulk SMS credentials are not configured.');
        }

        $response = Http::asForm()
            ->timeout(15)
            ->acceptJson()
            ->post($baseUrl, [
                'username' => $username,
                'password' => $password,
                'message' => $message,
                'sender' => $sender,
                'mobiles' => $this->normalizePhone($phone),
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Nigeria Bulk SMS request failed.');
        }

        $payload = $response->json();

        if (is_array($payload)) {
            $status = strtolower((string) ($payload['status'] ?? ''));
            $error = trim((string) ($payload['error'] ?? ''));

            if ($error !== '') {
                throw new RuntimeException($error);
            }

            if ($status !== '' && !in_array($status, ['ok', 'success'], true)) {
                throw new RuntimeException('Nigeria Bulk SMS rejected the message request.');
            }

            if ($status === '') {
                throw new RuntimeException('Nigeria Bulk SMS returned an unexpected response.');
            }

            return;
        }

        throw new RuntimeException('Nigeria Bulk SMS returned an invalid response.');
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
