<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Throwable;
use RuntimeException;

class AuthSecurityService
{
    public function __construct(
        protected NigeriaBulkSmsService $nigeriaBulkSmsService,
    ) {}

    public function issuePasswordResetToken(User $user, string $channel): string
    {
        $token = $this->generateNumericToken();

        $user->forceFill([
            'password_reset_channel' => $channel,
            'password_reset_token' => Hash::make($token),
            'password_reset_token_expires_at' => now()->addMinutes(
                (int) env('PASSWORD_RESET_TOKEN_TTL_MINUTES', 30)
            ),
        ])->save();

        $this->dispatchToken($user, $channel, $token, 'password_reset');

        return $token;
    }

    public function resetPassword(User $user, string $channel, string $token, string $password): void
    {
        if ($user->password_reset_channel !== $channel) {
            throw new RuntimeException('Invalid password reset request.');
        }

        if (
            !$user->password_reset_token ||
            !$user->password_reset_token_expires_at ||
            now()->greaterThan($user->password_reset_token_expires_at) ||
            !Hash::check($token, $user->password_reset_token)
        ) {
            throw new RuntimeException('Invalid or expired password reset token.');
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'password_reset_channel' => null,
            'password_reset_token' => null,
            'password_reset_token_expires_at' => null,
            'remember_token' => Str::random(10),
        ])->save();
    }

    public function issueVerificationToken(User $user, string $channel): string
    {
        if ($channel !== 'phone') {
            throw new RuntimeException('Phone verification uses token delivery. Email verification uses a link.');
        }

        $token = $this->generateNumericToken();

        $user->forceFill([
            'verification_channel' => $channel,
            'verification_token' => Hash::make($token),
            'verification_token_expires_at' => now()->addMinutes(
                (int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10)
            ),
        ])->save();

        $this->dispatchToken($user, $channel, $token, 'verification');

        return $token;
    }

    public function sendEmailVerificationLink(User $user): string
    {
        if (!$user->email) {
            throw new RuntimeException('Add an email address before requesting email verification.');
        }

        $user->forceFill([
            'verification_channel' => 'email',
            'verification_token' => null,
            'verification_token_expires_at' => now()->addMinutes(
                (int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10)
            ),
        ])->save();

        $link = URL::temporarySignedRoute(
            'api.auth.verify-email',
            now()->addMinutes((int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10)),
            [
                'user' => $user->id,
                'hash' => sha1((string) $user->email),
            ]
        );

        Mail::raw(
            "Verify your email by visiting this link: {$link}",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Asaba Hustle Email Verification');
            }
        );

        return $link;
    }

    public function verifyContact(User $user, string $channel, string $token): void
    {
        if ($channel !== 'phone') {
            throw new RuntimeException('Email verification is completed through the verification link.');
        }

        if ($user->verification_channel !== $channel) {
            throw new RuntimeException('Invalid verification request.');
        }

        if (
            !$user->verification_token ||
            !$user->verification_token_expires_at ||
            now()->greaterThan($user->verification_token_expires_at) ||
            !Hash::check($token, $user->verification_token)
        ) {
            throw new RuntimeException('Invalid or expired verification token.');
        }

        $attributes = [
            'verification_channel' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ];

        if ($channel === 'email') {
            $attributes['email_verified_at'] = now();
        }

        if ($channel === 'phone') {
            $attributes['phone_verified_at'] = now();
        }

        $user->forceFill($attributes)->save();
    }

    public function verifyEmailLink(User $user, string $hash): void
    {
        if (!$user->email || !hash_equals(sha1($user->email), $hash)) {
            throw new RuntimeException('Invalid email verification link.');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'verification_channel' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ])->save();
    }

    protected function dispatchToken(User $user, string $channel, string $token, string $purpose): void
    {
        if ($channel === 'email') {
            Mail::raw(
                "Your {$purpose} token is {$token}.",
                function ($message) use ($user, $purpose) {
                    $message->to($user->email)->subject('Asaba Hustle ' . ucfirst(str_replace('_', ' ', $purpose)));
                }
            );

            return;
        }

        try {
            $this->nigeriaBulkSmsService->send(
                (string) $user->phone,
                $this->buildPhoneTokenMessage($token, $purpose)
            );
        } catch (Throwable $exception) {
            Log::error('auth_phone_token_dispatch_failed', [
                'user_id' => $user->id,
                'channel' => $channel,
                'purpose' => $purpose,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to send the phone verification code right now. Please try again.');
        }

        Log::info('auth_token_dispatched', [
            'user_id' => $user->id,
            'channel' => $channel,
            'purpose' => $purpose,
        ]);
    }

    protected function buildPhoneTokenMessage(string $token, string $purpose): string
    {
        return match ($purpose) {
            'verification' => "Your Asaba Hustle verification code is {$token}. It expires soon. Do not share this code.",
            'password_reset' => "Your Asaba Hustle password reset code is {$token}. It expires soon. Do not share this code.",
            default => "Your Asaba Hustle code is {$token}.",
        };
    }

    protected function generateNumericToken(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
