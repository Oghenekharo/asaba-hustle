<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\JobApplication;
use App\Models\JobNegotiation;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $skills = Skill::query()->pluck('id', 'name');

        $client = $this->seedUser([
            'name' => 'Amara Okafor',
            'phone' => '08000000010',
            'email' => 'client@asabahustle.test',
            'password' => Hash::make('password123'),
            'primary_skill_id' => $skills['Cleaning'] ?? null,
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'bio' => 'Client account for seeded negotiation, hiring, messaging, and payment flows.',
            'latitude' => 6.1999,
            'longitude' => 6.7300,
        ], 'client');

        $workerAvailable = $this->seedUser([
            'name' => 'Chinedu Eze',
            'phone' => '08000000020',
            'email' => 'worker.available@asabahustle.test',
            'password' => Hash::make('password123'),
            'primary_skill_id' => $skills['Cleaning'] ?? null,
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'bio' => 'Experienced residential cleaner available for same-day jobs.',
            'rating' => 4.7,
            'latitude' => 6.2050,
            'longitude' => 6.7350,
        ], 'worker');

        $workerAssigned = $this->seedUser([
            'name' => 'Ifeanyi Nwosu',
            'phone' => '08000000021',
            'email' => 'worker.assigned@asabahustle.test',
            'password' => Hash::make('password123'),
            'primary_skill_id' => $skills['Electrical'] ?? null,
            'availability_status' => 'busy',
            'account_status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'bio' => 'Certified electrician handling seeded assigned jobs.',
            'rating' => 4.4,
            'latitude' => 6.2100,
            'longitude' => 6.7410,
        ], 'worker');

        $workerInProgress = $this->seedUser([
            'name' => 'Ejiro Oghene',
            'phone' => '08000000022',
            'email' => 'worker.progress@asabahustle.test',
            'password' => Hash::make('password123'),
            'primary_skill_id' => $skills['Painting'] ?? null,
            'availability_status' => 'busy',
            'account_status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'bio' => 'Interior painter currently executing seeded in-progress work.',
            'rating' => 4.8,
            'latitude' => 6.2120,
            'longitude' => 6.7420,
        ], 'worker');

        $workerRated = $this->seedUser([
            'name' => 'Mabel Adegoke',
            'phone' => '08000000023',
            'email' => 'worker.rated@asabahustle.test',
            'password' => Hash::make('password123'),
            'primary_skill_id' => $skills['Moving Help'] ?? null,
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'bio' => 'Reliable mover used for completed payment and rating scenarios.',
            'rating' => 4.9,
            'latitude' => 6.2080,
            'longitude' => 6.7380,
        ], 'worker');

        $pendingPhoneUser = $this->seedUser([
            'name' => 'Ngozi Onuoha',
            'phone' => '08000000030',
            'email' => null,
            'password' => Hash::make('password123'),
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => false,
            'verification_channel' => 'phone',
            'verification_token' => Hash::make('123456'),
            'verification_token_expires_at' => now()->addMinutes((int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10)),
            'bio' => 'Worker account pending phone verification for seeded auth tests.',
        ], 'worker');

        $pendingEmailUser = $this->seedUser([
            'name' => 'Tosin Balogun',
            'phone' => '08000000031',
            'email' => 'pending.email@asabahustle.test',
            'password' => Hash::make('password123'),
            'availability_status' => 'available',
            'account_status' => 'active',
            'is_verified' => false,
            'verification_channel' => 'email',
            'verification_token' => null,
            'verification_token_expires_at' => now()->addMinutes((int) env('CONTACT_VERIFICATION_TOKEN_TTL_MINUTES', 10)),
            'bio' => 'Client account pending email verification for seeded auth tests.',
        ], 'client');

        $this->seedJobsAndRelatedData(
            $skills,
            $client,
            $workerAvailable,
            $workerAssigned,
            $workerInProgress,
            $workerRated
        );

        $this->seedNotifications($client, $workerAvailable, $workerAssigned);
        $this->seedActivityLogs($client, $workerAvailable, $pendingPhoneUser, $pendingEmailUser);
    }

    protected function seedJobsAndRelatedData(
        \Illuminate\Support\Collection $skills,
        User $client,
        User $workerAvailable,
        User $workerAssigned,
        User $workerInProgress,
        User $workerRated
    ): void {
        $openCashJob = $this->seedJob([
            'user_id' => $client->id,
            'skill_id' => $skills['Cleaning'] ?? null,
            'title' => 'Deep Cleaning for Three-Bedroom Apartment',
            'description' => 'Client needs a careful cleaner for a full apartment reset before new tenants move in.',
            'budget' => 18000,
            'agreed_amount' => null,
            'location' => 'Okpanam Road, Asaba',
            'latitude' => 6.2041,
            'longitude' => 6.7339,
            'payment_method' => 'cash',
            'status' => ServiceJob::STATUS_OPEN,
            'assigned_to' => null,
        ]);

        $openTransferJob = $this->seedJob([
            'user_id' => $client->id,
            'skill_id' => $skills['Plumbing'] ?? null,
            'title' => 'Kitchen Sink Leak Repair',
            'description' => 'Client needs a plumber to replace a faulty connector and stop recurring leakage under the sink.',
            'budget' => 25000,
            'agreed_amount' => null,
            'location' => 'Summit Road, Asaba',
            'latitude' => 6.2102,
            'longitude' => 6.7412,
            'payment_method' => 'transfer',
            'status' => ServiceJob::STATUS_OPEN,
            'assigned_to' => null,
        ]);

        $assignedJob = $this->seedJob([
            'user_id' => $client->id,
            'skill_id' => $skills['Electrical'] ?? null,
            'title' => 'Living Room Socket Rewiring',
            'description' => 'Assigned electrical job awaiting worker confirmation and site access scheduling.',
            'budget' => 32000,
            'agreed_amount' => 30000,
            'location' => 'DBS Road, Asaba',
            'latitude' => 6.2150,
            'longitude' => 6.7440,
            'payment_method' => 'cash',
            'status' => ServiceJob::STATUS_ASSIGNED,
            'assigned_to' => $workerAssigned->id,
        ]);

        $inProgressJob = $this->seedJob([
            'user_id' => $client->id,
            'skill_id' => $skills['Painting'] ?? null,
            'title' => 'Interior Wall Repaint for Upstairs Flat',
            'description' => 'Painting job already started, used for seeded chat and progress tracking.',
            'budget' => 40000,
            'agreed_amount' => 42000,
            'location' => 'Anwai Road, Asaba',
            'latitude' => 6.2175,
            'longitude' => 6.7461,
            'payment_method' => 'cash',
            'status' => ServiceJob::STATUS_IN_PROGRESS,
            'assigned_to' => $workerInProgress->id,
        ]);

        $ratedJob = $this->seedJob([
            'user_id' => $client->id,
            'skill_id' => $skills['Moving Help'] ?? null,
            'title' => 'Two-Bedroom Apartment Relocation',
            'description' => 'Completed moving assistance job with settled payment and client review.',
            'budget' => 55000,
            'agreed_amount' => 58000,
            'location' => 'Nnebisi Road, Asaba',
            'latitude' => 6.2068,
            'longitude' => 6.7368,
            'payment_method' => 'transfer',
            'status' => ServiceJob::STATUS_RATED,
            'assigned_to' => $workerRated->id,
        ]);

        JobApplication::query()->updateOrCreate(
            ['job_id' => $openCashJob->id, 'user_id' => $workerAvailable->id],
            ['message' => 'I can arrive this afternoon with all cleaning supplies and complete the job in one visit.', 'status' => 'pending']
        );

        JobApplication::query()->updateOrCreate(
            ['job_id' => $openTransferJob->id, 'user_id' => $workerAssigned->id],
            ['message' => 'I can inspect the leakage quickly and replace the damaged fitting immediately.', 'status' => 'pending']
        );

        $this->seedNegotiation(
            $openCashJob,
            $client,
            $workerAvailable,
            16500,
            'I can handle the cleaning today for NGN 16,500 and bring my own supplies.',
            [],
            'pending',
            'worker'
        );

        $this->seedNegotiation(
            $openTransferJob,
            $client,
            $workerAssigned,
            23000,
            'I can fix the sink leak for NGN 23,000 if the replacement part is standard.',
            [
                [
                    'actor' => 'worker',
                    'status' => 'pending',
                    'amount' => 26000,
                    'message' => 'Initial inspection and repair will cost NGN 26,000.',
                    'recorded_at' => now()->subDays(2)->toIso8601String(),
                ],
                [
                    'actor' => 'client',
                    'status' => 'rejected',
                    'amount' => 23000,
                    'message' => 'Budget is tighter than expected. I can do NGN 23,000 if you can still supply the connector.',
                    'recorded_at' => now()->subDay()->toIso8601String(),
                ],
            ],
            'pending',
            'worker'
        );

        $this->seedNegotiation(
            $assignedJob,
            $client,
            $workerAssigned,
            30000,
            'Accepted NGN 30,000 for the socket rewiring and materials.',
            [
                [
                    'actor' => 'worker',
                    'status' => 'pending',
                    'amount' => 34000,
                    'message' => 'I can handle the rewiring safely for NGN 34,000.',
                    'recorded_at' => now()->subDays(3)->toIso8601String(),
                ],
                [
                    'actor' => 'client',
                    'status' => 'rejected',
                    'amount' => 30000,
                    'message' => 'Please work with NGN 30,000 and I will provide site access early.',
                    'recorded_at' => now()->subDays(2)->toIso8601String(),
                ],
            ],
            'accepted',
            'client'
        );

        $this->seedNegotiation(
            $inProgressJob,
            $client,
            $workerInProgress,
            42000,
            'Work is already underway based on the accepted NGN 42,000 agreement.',
            [
                [
                    'actor' => 'worker',
                    'status' => 'pending',
                    'amount' => 45000,
                    'message' => 'My quote for the repaint is NGN 45,000 including surface prep.',
                    'recorded_at' => now()->subDays(4)->toIso8601String(),
                ],
                [
                    'actor' => 'client',
                    'status' => 'rejected',
                    'amount' => 42000,
                    'message' => 'I can approve NGN 42,000 if the primer is included.',
                    'recorded_at' => now()->subDays(3)->toIso8601String(),
                ],
            ],
            'accepted',
            'worker'
        );

        $this->seedNegotiation(
            $ratedJob,
            $client,
            $workerRated,
            58000,
            'Move completed successfully on the accepted NGN 58,000 agreement.',
            [
                [
                    'actor' => 'worker',
                    'status' => 'pending',
                    'amount' => 60000,
                    'message' => 'Full move with loading support will cost NGN 60,000.',
                    'recorded_at' => now()->subDays(6)->toIso8601String(),
                ],
                [
                    'actor' => 'client',
                    'status' => 'rejected',
                    'amount' => 58000,
                    'message' => 'I can close at NGN 58,000 if the team arrives before 8 AM.',
                    'recorded_at' => now()->subDays(5)->toIso8601String(),
                ],
            ],
            'accepted',
            'client'
        );

        $assignedConversation = Conversation::query()->updateOrCreate(
            ['job_id' => $assignedJob->id],
            ['client_id' => $client->id, 'worker_id' => $workerAssigned->id]
        );

        $progressConversation = Conversation::query()->updateOrCreate(
            ['job_id' => $inProgressJob->id],
            ['client_id' => $client->id, 'worker_id' => $workerInProgress->id]
        );

        $ratedConversation = Conversation::query()->updateOrCreate(
            ['job_id' => $ratedJob->id],
            ['client_id' => $client->id, 'worker_id' => $workerRated->id]
        );

        $this->seedMessage($assignedConversation, $client, 'Please confirm your availability for tomorrow morning.', true);
        $this->seedMessage($assignedConversation, $workerAssigned, 'Confirmed. I will be there by 9 AM.', false);
        $this->seedMessage($progressConversation, $client, 'How far along is the painting work?', true);
        $this->seedMessage($progressConversation, $workerInProgress, 'I have completed the first coat and will finish today.', false);
        $this->seedMessage($ratedConversation, $client, 'Thanks for helping with the move.', true);
        $this->seedMessage($ratedConversation, $workerRated, 'Happy to help. Let me know if you need anything else.', true);

        Payment::query()->updateOrCreate(
            ['reference' => 'AH_SEED_TRANSFER_SUCCESS'],
            [
                'job_id' => $ratedJob->id,
                'user_id' => $client->id,
                'amount' => $ratedJob->agreed_amount ?? $ratedJob->budget,
                'payment_method' => 'transfer',
                'status' => Payment::STATUS_SUCCESSFUL,
                'idempotency_key' => (string) Str::uuid(),
                'verified_at' => now()->subDay(),
                'provider_payload' => [
                    'status' => 'confirmed',
                    'channel' => 'bank_transfer',
                    'confirmed_by' => 'demo-seeder',
                ],
            ]
        );

        Payment::query()->updateOrCreate(
            ['reference' => 'AH_SEED_CASH_PENDING'],
            [
                'job_id' => $openTransferJob->id,
                'user_id' => $client->id,
                'amount' => $openTransferJob->budget,
                'payment_method' => 'cash',
                'status' => Payment::STATUS_PENDING,
                'idempotency_key' => 'seed-cash-pending',
                'verified_at' => null,
                'provider_payload' => [
                    'status' => 'awaiting_cash_settlement',
                    'recorded_by' => 'demo-seeder',
                ],
            ]
        );

        Rating::query()->updateOrCreate(
            ['job_id' => $ratedJob->id],
            [
                'client_id' => $client->id,
                'worker_id' => $workerRated->id,
                'rating' => 5,
                'review' => 'Reliable, punctual, and careful with fragile items.',
            ]
        );
    }

    protected function seedNotifications(User ...$users): void
    {
        $notifications = [
            [$users[0], 'New offer received', 'Chinedu Eze submitted a fresh cleaning offer on your open job.', 'job', false],
            [$users[0], 'Payment confirmed', 'Your apartment relocation payment was verified successfully.', 'payment', true],
            [$users[1], 'Offer submitted', 'Your cleaning offer was sent to Amara Okafor for review.', 'job', true],
            [$users[2], 'New message', 'You have a seeded conversation waiting on the rewiring job.', 'message', false],
        ];

        foreach ($notifications as [$user, $title, $message, $type, $isRead]) {
            UserNotification::query()->updateOrCreate(
                ['user_id' => $user->id, 'title' => $title],
                ['message' => $message, 'type' => $type, 'is_read' => $isRead]
            );
        }
    }

    protected function seedActivityLogs(User ...$users): void
    {
        $logs = [
            [$users[0], 'user_login', ['source' => 'demo-seeder'], '127.0.0.10'],
            [$users[0], 'job_posted', ['title' => 'Deep Cleaning for Three-Bedroom Apartment'], '127.0.0.10'],
            [$users[1], 'message_sent', ['job' => 'Interior Wall Repaint for Upstairs Flat'], '127.0.0.20'],
            [$users[2], 'verification_token_sent', ['channel' => 'phone'], '127.0.0.30'],
            [$users[3], 'verification_link_sent', ['channel' => 'email'], '127.0.0.31'],
        ];

        foreach ($logs as [$user, $action, $metadata, $ipAddress]) {
            ActivityLog::query()->updateOrCreate(
                ['user_id' => $user->id, 'action' => $action, 'ip_address' => $ipAddress],
                ['metadata' => $metadata]
            );
        }
    }

    protected function seedUser(array $attributes, string $role): User
    {
        $lookup = ['phone' => $attributes['phone']];

        $user = User::query()->firstOrNew($lookup);
        $user->forceFill($attributes)->save();
        $user->syncRoles([$role]);

        return $user;
    }

    protected function seedJob(array $attributes): ServiceJob
    {
        return ServiceJob::query()->updateOrCreate(
            ['title' => $attributes['title']],
            $attributes
        );
    }

    protected function seedMessage(Conversation $conversation, User $sender, string $message, bool $isRead): void
    {
        ChatMessage::query()->firstOrCreate(
            [
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'message' => $message,
            ],
            ['is_read' => $isRead]
        );
    }

    protected function seedNegotiation(
        ServiceJob $job,
        User $client,
        User $worker,
        float $amount,
        string $message,
        array $history,
        string $status,
        string $createdBy
    ): void {
        JobNegotiation::query()->updateOrCreate(
            [
                'job_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $worker->id,
            ],
            [
                'amount' => $amount,
                'message' => $message,
                'history' => $history,
                'status' => $status,
                'created_by' => $createdBy,
                'expires_at' => now()->addDays(3),
            ]
        );
    }
}
