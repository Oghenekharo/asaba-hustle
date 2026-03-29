# Asaba Hustle Agent Handoff

This file summarizes the repository state as of March 29, 2026.

## High-Level Summary

Asaba Hustle is a Laravel 12 local-services marketplace with:

- a JSON API for mobile and external clients
- a role-aware Blade web app
- an admin panel
- realtime chat, notifications, and job-status updates through Reverb
- negotiation-first hiring
- manual payment completion with `cash` and `transfer`
- legacy Paystack and Flutterwave support still present for compatibility

## Current Stack

- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- Spatie Laravel Permission
- Laravel Reverb
- Blade
- Tailwind CSS
- jQuery AJAX
- Vite
- MySQL for normal deployment
- SQLite for tests and fallback/local scenarios

## Current Product State

### Authentication and Verification

- phone and email registration still exist in the backend
- the web frontend is now SMS-first for user-facing verification and recovery
- phone verification is enforced for clients before posting jobs
- worker ID verification is enforced before applying to jobs
- `is_verified` remains admin-controlled platform verification
- OTPs are no longer returned in responses or logged for real environments
- Nigeria Bulk SMS is used directly through the app service layer instead of the old incompatible package

### Worker Profile Rules

Workers can manage:

- name
- bio
- primary skill
- extra skills
- availability
- ID upload
- payout details:
  - `bank_name`
  - `account_name`
  - `account_number`

Rules now enforced:

- workers can have at most 3 total skills
- workers must complete payout details before applying
- workers must be ID-verified before applying

### Job and Negotiation Flow

Current verified flow:

1. Verified client creates a job
2. Verified worker applies with `amount` and `message`
3. A negotiation row is created
4. Either side can counter on the same negotiation row
5. Client can reject, accept, or counter
6. Worker can accept or counter a client counter-offer
7. Once accepted, the job is assigned and `agreed_amount` is stored
8. Assigned worker accepts or rejects the assignment
9. Worker starts work
10. Worker marks work completed
11. Client marks payment sent
12. Worker confirms payment
13. Client and worker can both rate the other participant

Current job statuses:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`
- `cancelled`

Important behavior:

- once a negotiation has been accepted, stale negotiations are hidden from the web job detail flow
- workers can rate clients
- clients can rate workers

### Chat and Notifications

Chat is now available only when a job has an assigned worker and the job is in one of:

- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`

Chat behavior:

- assigned worker can initiate chat
- client can initiate chat after assignment
- conversations use UUID route keys
- conversation previews and unread states update in realtime

Notifications currently cover:

- new job application
- job matched to worker skills
- worker hired
- worker accepted assignment
- worker started job
- worker completed job
- client marked paid
- worker confirmed payment
- new chat message
- ratings submitted
- admin cancellation and rollback actions

### Admin

Admin currently manages:

- users
- jobs
- payments
- ratings
- activity logs
- dashboard summaries

Admin job controls now include:

- cancel for allowed active statuses
- rollback to earlier valid statuses
- job review with client and worker context

### Realtime Channels

Current broadcast channels:

- `private-conversation.{uuid}`
- `private-user.{id}`
- `private-job.{id}`

## Current API Surface

Main public/auth endpoints:

```text
POST /api/auth/register
POST /api/auth/login
POST /api/auth/forgot-password
POST /api/auth/reset-password
POST /api/auth/verify-phone
GET  /api/auth/verify-email/{user}/{hash}
```

Authenticated auth/profile endpoints:

```text
GET  /api/auth/me
POST /api/auth/logout
POST /api/auth/change-password
PUT  /api/auth/profile
POST /api/auth/upload-id
POST /api/auth/availability
POST /api/auth/send-verification-token
POST /api/auth/verify-contact
```

Job and negotiation endpoints:

```text
GET   /api/jobs
POST  /api/jobs
GET   /api/jobs/{job}
POST  /api/jobs/{job}/apply
POST  /api/jobs/{job}/negotiate
POST  /api/jobs/{job}/negotiate/{negotiation}/accept
POST  /api/jobs/{job}/negotiate/{negotiation}/counter
POST  /api/jobs/{job}/negotiate/{negotiation}/reject
POST  /api/jobs/{job}/hire
POST  /api/jobs/{job}/accept
POST  /api/jobs/{job}/reject
POST  /api/jobs/{job}/start
POST  /api/jobs/{job}/complete
POST  /api/jobs/{job}/mark-paid
POST  /api/jobs/{job}/confirm-payment
POST  /api/jobs/{job}/rate
PATCH /api/jobs/{job}/cancel
PATCH /api/jobs/{job}/rollback
GET   /api/jobs/{job}/suggested-workers
GET   /api/jobs/my/jobs
GET   /api/jobs/search
```

Messaging and notifications:

```text
GET  /api/messages/conversations
GET  /api/messages/conversation/{conversation}
POST /api/messages/send
POST /api/messages/conversation/{conversation}/read

GET  /api/notifications
POST /api/notifications/read
POST /api/notifications/read-all
```

## Deployment State

Current production setup is Docker + Apache, not Apache-served Laravel files and not Caddy as the live front door.

Production pieces:

- `docker-compose.prod.yml`
- `docker/php/Dockerfile.prod`
- `docker/php/entrypoint.prod.sh`
- `.env.production.example`
- `VPS_DEPLOYMENT_RUNBOOK.md`

Current VPS pattern:

- Apache terminates public traffic on `80/443`
- app container binds `127.0.0.1:8000`
- Reverb binds `127.0.0.1:8080`
- MySQL binds `127.0.0.1:3306`
- Apache proxies:
  - `hustle.currencyopts.com` -> `127.0.0.1:8000`
  - `ws.hustle.currencyopts.com` -> `127.0.0.1:8080`

## Important Files

Backend:

- `app/Services/JobService.php`
- `app/Services/NegotiationService.php`
- `app/Services/ChatService.php`
- `app/Services/AuthSecurityService.php`
- `app/Services/NigeriaBulkSmsService.php`
- `app/Policies/ServiceJobPolicy.php`
- `app/Http/Controllers/Web/JobController.php`
- `app/Http/Controllers/Web/NegotiationController.php`
- `app/Http/Controllers/Web/MessageController.php`
- `app/Http/Controllers/Api/ServiceJobController.php`
- `app/Http/Controllers/Api/JobNegotiationController.php`

Frontend:

- `resources/views/web/job-detail.blade.php`
- `resources/views/web/messages.blade.php`
- `resources/views/web/auth/verify-phone.blade.php`
- `resources/views/admin/jobs/show.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/js/main.js`
- `resources/js/app.js`

Database and seeders:

- `database/seeders/DemoDataSeeder.php`
- `database/seeders/ProductionSeeder.php`
- `database/migrations/2026_03_28_190000_expand_ratings_for_two_sided_feedback.php`

## Verification Pattern

Recent verification has mainly used:

- `php -l` on changed PHP and Blade-backed files
- `php artisan route:list` after route changes
- `npm run build` after JS or Blade interaction changes

Do not assume a full PHPUnit pass has been run after every change.

## Remaining Gaps

- broader automated coverage for negotiation, payment, and realtime flows
- more UI consistency for loading states across all admin and web action buttons
- more production observability and retry hardening
- deeper cleanup of legacy gateway paths if they are no longer needed

## One-Line Status

The project currently supports verified client job posting, verified worker application, negotiation-first hiring with counter-offers, assignment-based chat, manual closeout, two-sided ratings, realtime notifications, admin rollback/cancel tools, and Docker + Apache VPS deployment.
