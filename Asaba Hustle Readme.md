# Asaba Hustle

Asaba Hustle is a Laravel 12 marketplace for connecting clients with nearby workers for local service jobs.

This file tracks the current implementation state of the project.

## Current Product Snapshot

The platform currently includes:

- a Blade web app for clients and workers
- a JSON API for mobile and external use
- an admin panel
- realtime chat, notifications, and job status updates
- negotiation-first hiring
- manual payment closure using `cash` and `transfer`
- legacy Paystack and Flutterwave payment code retained for compatibility

## Major Current Rules

- only phone-verified clients can post jobs
- only ID-verified workers can apply
- workers must complete payout details before applying
- workers can have at most 3 total skills
- clients and workers can both rate each other after successful closeout
- chat opens only after a worker has been assigned
- transfer jobs require a receipt upload before payment can be marked sent

## Current Job Flow

1. Verified client creates a job
2. Verified worker applies with `amount` and `message`
3. A negotiation is created
4. Either side can counter on the same negotiation row
5. Client can reject, accept, or counter
6. Worker can accept or counter the client's counter-offer
7. Accepted negotiation assigns the worker and stores `agreed_amount`
8. Worker accepts or rejects the assignment
9. Worker starts the job
10. Worker marks it completed
11. Client marks payment sent
12. For transfer jobs, the client uploads a payment receipt and the worker reviews it
13. Worker confirms payment
14. Both parties can rate each other

Current job statuses:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`
- `cancelled`

## Current Messaging And Notifications

Messaging now supports:

- conversation list
- conversation thread loading
- AJAX send
- unread state updates
- realtime message broadcasts
- client-initiated chat after assignment
- worker-initiated chat after assignment

Notifications now cover:

- job matches worker skills
- new job applications
- worker hired
- worker accepted assignment
- worker started job
- worker completed job
- client marked paid
- worker confirmed payment
- new chat message
- rating submitted
- admin rollback and cancellation actions
- negotiation updates and job-linked push notifications

## Current Admin Coverage

Admin areas currently include:

- dashboard
- users
- jobs
- payments
- ratings
- activity logs

Admin job tooling now includes:

- cancellation on allowed active statuses
- rollback to earlier valid job stages
- richer job review with client and worker context

## Current Local Docker Setup

Local Docker files:

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `.env.docker`
- `.env.docker.prodlike.example`
- `.dockerignore`

Local services:

- `mysql`
- `redis`
- `mailhog`
- `app`
- `queue`
- `vite`
- optional `reverb`

Typical local commands:

- `docker compose --env-file .env.docker --profile local up --build`
- `docker compose --env-file .env.docker --profile local --profile realtime up --build`

## Current Production Deployment Shape

Production uses Docker plus Apache on the VPS.

Important files:

- `docker-compose.prod.yml`
- `docker/php/Dockerfile.prod`
- `docker/php/entrypoint.prod.sh`
- `.env.production.example`
- `VPS_DEPLOYMENT_RUNBOOK.md`

Current production domains:

- `hustle.currencyopts.com`
- `ws.hustle.currencyopts.com`

Runtime pattern:

- Apache terminates public traffic
- app container listens on `127.0.0.1:8000`
- Reverb listens on `127.0.0.1:8080`
- MySQL listens on `127.0.0.1:3306`
- Apache reverse-proxies the app and websocket domains

Use the full deployment instructions in:

- `VPS_DEPLOYMENT_RUNBOOK.md`

## Important Current Files

Backend:

- `app/Services/JobService.php`
- `app/Services/NegotiationService.php`
- `app/Services/ChatService.php`
- `app/Services/UserNotificationService.php`
- `app/Services/NigeriaBulkSmsService.php`
- `app/Policies/ServiceJobPolicy.php`
- `app/Http/Controllers/Web/JobController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/ServiceJobController.php`
- `app/Http/Controllers/Api/JobNegotiationController.php`

Frontend:

- `resources/views/web/job-detail.blade.php`
- `resources/views/web/job-detail/partials/lifecycle.blade.php`
- `resources/views/web/job-detail/partials/rating.blade.php`
- `resources/views/web/messages.blade.php`
- `resources/views/admin/jobs/show.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/js/main.js`
- `resources/js/app.js`

Database:

- `database/seeders/DemoDataSeeder.php`
- `database/seeders/ProductionSeeder.php`
- `database/migrations/2026_03_28_190000_expand_ratings_for_two_sided_feedback.php`

## Remaining Gaps

- broader automated coverage
- more loading-state consistency across click-triggered UI actions
- more production observability
- further cleanup of legacy gateway code if no longer needed


