# Asaba Hustle

Asaba Hustle is a Laravel 12 marketplace for connecting clients with nearby workers for local service jobs.

This file is the project progress and implementation snapshot.

## Current Architecture

The codebase is currently split across:

- API controllers for mobile/external clients
- focused web controllers for browser flows
- services for business rules
- request classes and policies for validation/authorization
- Blade views for page rendering
- jQuery AJAX in the web app
- Reverb/Echo for realtime updates

## Local Docker

Local container support is now available through:

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `.env.docker`
- `.env.docker.prodlike.example`
- `.dockerignore`

Available services:

- `mysql` on port `3306` as the default database
- `redis` on port `6379`
- `mailhog` SMTP on `1025` and MailHog UI on `8025`
- `app` on port `8000`
- `queue` for background jobs
- `vite` on port `5173`
- optional `reverb` profile on port `8080`
- `composer_deps` and `npm_deps` as one-time dependency/bootstrap helpers

Typical local startup:

1. Run `docker compose --env-file .env.docker --profile local up --build`
2. Use `docker compose --env-file .env.docker --profile local --profile realtime up --build` when you want Reverb enabled

For production-like container runs against external services:

1. Copy `.env.docker.prodlike.example` to a real env file
2. Replace the placeholder MySQL, Redis, SMTP, and Reverb values
3. Run `docker compose --env-file your-prodlike-env-file up --build`

The local profile uses MySQL and auto-handles bootstrap tasks such as hydrating `vendor` and `node_modules`, clearing cached Laravel config, waiting for MySQL, running first-run-only `php artisan migrate:fresh --seed --force`, and creating the storage symlink.

SQLite remains optional for container use by overriding `DB_CONNECTION=sqlite` and `DB_DATABASE=/var/www/html/database/database.sqlite`. In that mode the app container creates the SQLite database file automatically before migrations.

Realtime Docker wiring now distinguishes container and browser hosts:

- Laravel containers publish to Reverb using `REVERB_HOST=reverb`
- the browser connects using `VITE_REVERB_HOST=127.0.0.1`

The old `AppController` has already been split into dedicated web controllers such as:

- `DashboardController`
- `ProfileController`
- `JobController`
- `NegotiationController`
- `MessageController`
- `NotificationController`
- `WebPaymentController`

## Implemented Product Flow

### Authentication and Verification

- phone and email registration
- phone verification
- email verification
- forgot/reset password
- Sanctum API auth
- role support for `client`, `worker`, and `admin`
- admin-controlled `is_verified`

### Jobs and Negotiation

The hiring flow is now negotiation-first.

Implemented:

- clients create jobs
- workers apply with `amount` and `message`
- application creates the initial offer
- direct negotiation endpoint supports additional offers/counters
- client rejection requires:
  - a reason/message
  - a counter amount
- worker counters reuse the same negotiation row
- prior states are appended to `job_negotiations.history`
- client acceptance assigns the worker and stores `service_jobs.agreed_amount`
- assigned workers can reject before accepting the job, which reopens the job

Current statuses:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`

### Payments

Current service-job payment methods:

- `cash`
- `transfer`

Current closeout flow:

- assigned worker can accept or reject while status is `assigned`
- worker completes the job
- client marks payment sent
- worker confirms payment receipt
- client rates the worker

Important note:

- Paystack and Flutterwave code still exist in the backend
- webhook and verification code remains available
- but current service-job creation validates only `cash` and `transfer`

### Messaging and Notifications

Implemented:

- conversation listing
- conversation messages
- AJAX send
- read state handling
- realtime message updates
- realtime notification updates
- job status broadcasts on job detail

### Admin

Admin panel currently includes:

- dashboard
- users
- jobs
- payments
- ratings
- activity logs

Recent admin updates:

- payment pages now emphasize manual methods
- legacy gateway methods remain visible as legacy filters
- dashboard payment card reflects settled volume
- admin cancellation is now restricted to `open`, `assigned`, `worker_accepted`, and `in_progress`

### Activity Logging

Audit coverage was extended beyond auth/chat.

Now logged in the service layer:

- job creation
- job application submission
- worker hire
- worker assignment acceptance
- job start
- job completion
- payment marked sent
- payment confirmed
- worker rating
- negotiation created
- negotiation countered
- negotiation accepted
- negotiation rejected
- gateway payment initialization
- gateway payment verification success/failure

## Seeded Demo Data

The demo seeder now uses realistic full names and coherent relationships.

Seed data includes:

- named client and workers
- jobs that belong to the seeded client
- assigned/in-progress/rated jobs that point to the correct worker
- negotiation data aligned to seeded jobs
- related conversations, messages, ratings, notifications, payments, and activity logs

## Current API Workflow

Recommended API happy path:

1. Login as client and worker.
2. Client creates a job.
3. Worker applies with `amount` and `message`.
4. Client reviews the negotiation.
5. Client rejects with counter amount and reason, or accepts directly.
6. Worker counters again if needed.
7. Client accepts the negotiation.
8. Worker accepts the job.
9. Worker starts the job.
10. Worker completes the job.
11. Client marks paid.
12. Worker confirms payment.
13. Client rates worker.

Negotiation endpoints:

- `POST /api/jobs/{job}/negotiate`
- `POST /api/jobs/{job}/negotiate/{negotiation}/accept`
- `POST /api/jobs/{job}/negotiate/{negotiation}/reject`

Job lifecycle endpoints:

- `POST /api/jobs/{job}/apply`
- `POST /api/jobs/{job}/accept`
- `POST /api/jobs/{job}/reject`
- `POST /api/jobs/{job}/start`
- `POST /api/jobs/{job}/complete`
- `POST /api/jobs/{job}/mark-paid`
- `POST /api/jobs/{job}/confirm-payment`
- `POST /api/jobs/{job}/rate`
- `PATCH /api/jobs/{job}/cancel` for admin users only

## Key Files

Backend:

- `app/Services/JobService.php`
- `app/Services/NegotiationService.php`
- `app/Services/PaymentService.php`
- `app/Services/ActivityLogService.php`
- `app/Http/Controllers/Api/ServiceJobController.php`
- `app/Http/Controllers/Api/JobNegotiationController.php`
- `routes/api.php`
- `routes/web.php`

Frontend:

- `resources/views/web/job-detail.blade.php`
- `resources/views/admin/payments/index.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/js/main.js`

Database:

- `database/seeders/DemoDataSeeder.php`
- `database/factories/UserFactory.php`
- `database/factories/ServiceJobFactory.php`
- `database/factories/JobNegotiationFactory.php`

## VPS Production Deployment

Production deployment files now included:

- `docker/php/Dockerfile.prod`
- `docker/php/entrypoint.prod.sh`
- `docker/caddy/Caddyfile`
- `docker-compose.prod.yml`
- `.env.production.example`

Recommended production hostnames:

- `hustle.currencyopts.com`
- `ws.hustle.currencyopts.com`

Recommended rollout:

1. Copy `.env.production.example` to `.env.production`
2. Set `APP_KEY`, database credentials, SMTP credentials, image name/tag, and Reverb secrets
3. Point both DNS records to the VPS
4. Install Docker Engine and Docker Compose plugin on the VPS
5. Build with:
   - `docker compose -f docker-compose.prod.yml --env-file .env.production build`
6. Or push/pull a tagged image:
   - `docker push yourdockerhubname/asaba-hustle:latest`
   - `docker compose -f docker-compose.prod.yml --env-file .env.production pull`
7. Start with:
   - `docker compose -f docker-compose.prod.yml --env-file .env.production up -d`

Production runtime layout:

- `app` serves the web app
- `queue` handles queued jobs
- `reverb` handles websockets
- `caddy` terminates HTTPS and proxies the two domains
- `mysql` and `redis` are included by default

Operational notes:

- keep `RUN_MIGRATIONS=true` for the first production boot, then switch to `false` if you want manual schema rollout control
- frontend assets are built into the production image
- use real SMTP in production
- keep MySQL, Redis, and Reverb private to the Docker network

## Remaining Gaps

- more automated tests around negotiation transitions
- more cleanup of stale legacy references
- stronger production hardening and observability
