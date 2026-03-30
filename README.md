# Asaba Hustle

Asaba Hustle is a Laravel 12 local-services marketplace that connects clients with nearby workers for everyday jobs.

The project now has:

- a role-aware Blade web app
- a JSON API for mobile/external clients
- an admin panel
- realtime chat, notifications, and job status updates
- negotiation-first hiring
- manual payment completion with `cash` and `transfer`
- legacy Paystack and Flutterwave integration still present in code

## Local Docker

A local Docker setup now exists in:

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `.env.docker`
- `.env.docker.prodlike.example`
- `.dockerignore`

Default local containers:

- `mysql` as the primary database on `127.0.0.1:3306`
- `redis` on `127.0.0.1:6379`
- `mailhog` SMTP on `127.0.0.1:1025` with inbox UI on `http://127.0.0.1:8025`
- `app` for Laravel HTTP on `http://127.0.0.1:8000`
- `queue` for queued jobs
- `vite` for frontend assets on `http://127.0.0.1:5173`
- optional `reverb` profile for realtime on port `8080`

Start the full local stack with bundled MySQL, Redis, and MailHog:

- `docker compose --env-file .env.docker --profile local up --build`
- `docker compose --env-file .env.docker --profile local --profile realtime up --build` if you also want Reverb

Dependency containers are used during startup:

- `composer_deps` installs PHP packages into the shared `vendor` volume only when `composer.lock` changes
- `npm_deps` installs frontend packages into the shared `node_modules` volume only when `package-lock.json` or `package.json` changes
- `app`, `queue`, and `reverb` reuse those shared volumes instead of reinstalling dependencies on each boot

Run the same app containers against external services in a production-like setup:

1. Copy `.env.docker.prodlike.example` to your own env file such as `.env.docker.prodlike`
2. Fill in external MySQL, Redis, SMTP, and optional Reverb values
3. Start with `docker compose --env-file .env.docker.prodlike up --build`

The local profile uses MySQL by default and auto-handles:

- dependency hydration through `composer_deps` and `npm_deps`
- database readiness waits for MySQL-backed services
- `php artisan optimize:clear`
- first-run-only `php artisan migrate:fresh --seed --force`
- `php artisan storage:link`

Docker local mail and Redis defaults are also wired in:

- `MAIL_MAILER=smtp`
- `MAIL_HOST=mailhog`
- `MAIL_PORT=1025`
- `REDIS_HOST=redis`
- `REDIS_PORT=6379`
- `BROADCAST_CONNECTION=reverb`
- `REVERB_HOST=reverb` inside containers
- `VITE_REVERB_HOST=127.0.0.1` for the browser websocket client

SQLite is still available as an optional fallback by overriding the container env, for example:

- `DB_CONNECTION=sqlite`
- `DB_DATABASE=/var/www/html/database/database.sqlite`

You can either export those variables before startup or create a second file like `.env.docker.sqlite` and run:

- `docker compose --env-file .env.docker.sqlite up --build`

When `DB_CONNECTION=sqlite`, the app container will create `database/database.sqlite` automatically before migrations run.

## Current Status

Core flows currently working in the codebase:

- auth with phone/email verification
- admin-controlled identity approval
- client job posting
- worker application with initial offer amount
- negotiation create, reject-with-counter, counter again on the same record, and accept
- worker assignment from accepted negotiation
- worker accept, reject, start, complete
- client mark paid
- worker confirm payment
- client rate worker
- admin review of users, jobs, payments, ratings, and activity logs
- admin cancellation limited to `open`, `assigned`, `worker_accepted`, and `in_progress`

Recent structural updates:

- the old monolithic web controller was split into focused web controllers
- negotiation history is stored on the negotiation row as JSON
- agreed fee is stored on `service_jobs.agreed_amount`
- seeded data now uses realistic client and worker names
- seeded jobs and related records are relationship-consistent
- admin payment reporting now reflects manual payment methods first
- activity logs now capture more job, negotiation, and payment transitions

## Current Job Workflow

Recommended live workflow:

1. Client creates a job with `payment_method` of `cash` or `transfer`.
2. Worker applies with `message` and `amount`.
3. A negotiation row is created for that worker/job pair.
4. Client can reject with a required counter amount and reason.
5. Worker can counter again on the same negotiation row.
6. Client accepts the negotiation.
7. Job moves to `assigned` and stores `agreed_amount`.
8. Worker can accept or reject the assignment while the job is still `assigned`.
9. If the worker rejects, the job reopens and the accepted offer is marked rejected.
10. If the worker accepts, work can start and later be completed.
11. Client marks payment sent.
12. Worker confirms payment.
13. Client rates the worker.

Current job statuses:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`

## Payments

Current service-job payment methods:

- `cash`
- `transfer`

Manual payment flow:

- client marks the job as paid
- a payment record is created with the job payment method
- worker confirms receipt
- payment becomes successful

Legacy gateway support still exists in the backend:

- Paystack initialize and verify
- Flutterwave initialize and verify
- webhook handlers
- provider payload persistence

Those gateway endpoints remain available in code, but they are no longer the default service-job workflow and are not the primary seeded/demo path.

## Realtime

Realtime updates are implemented for:

- messages
- user notifications
- job status changes

Channel patterns in use:

- `private-conversation.{uuid}`
- `private-user.{id}`
- `private-job.{id}`

## Admin

Admin areas currently cover:

- dashboard
- users and verification
- jobs
- payments
- ratings
- activity logs

Admin payment reporting now supports:

- `cash`
- `transfer`
- legacy `paystack`
- legacy `flutterwave`

## Important Files

Backend:

- `app/Services/JobService.php`
- `app/Services/NegotiationService.php`
- `app/Services/PaymentService.php`
- `app/Services/ActivityLogService.php`
- `app/Http/Controllers/Api/ServiceJobController.php`
- `app/Http/Controllers/Api/JobNegotiationController.php`
- `app/Http/Controllers/Web/JobController.php`
- `app/Http/Controllers/Web/NegotiationController.php`
- `app/Http/Controllers/Web/WebPaymentController.php`

Frontend:

- `resources/views/web/job-detail.blade.php`
- `resources/views/admin/payments/index.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/js/main.js`

Database:

- `database/seeders/DemoDataSeeder.php`
- `database/factories/*`
- `database/migrations/2026_03_19_120000_update_service_job_payment_flow.php`
- `database/migrations/2026_03_25_203000_add_negotiation_lookup_indexes.php`
- `database/migrations/2026_03_25_210000_add_history_to_job_negotiations_and_agreed_amount_to_service_jobs.php`

## Local Verification Pattern

Typical verification used recently:

- `php -l` on touched PHP files
- `php artisan route:list` when routes/controllers change
- `npm run build` when frontend assets change

## VPS Production Deployment

Production deployment artifacts now exist in:

- `docker/php/Dockerfile.prod`
- `docker/php/entrypoint.prod.sh`
- `docker/caddy/Caddyfile`
- `docker-compose.prod.yml`
- `.env.production.example`

Recommended production domains:

- `hustle.currencyopts.com` for the app
- `ws.hustle.currencyopts.com` for Reverb websockets

Production flow:

1. Copy `.env.production.example` to `.env.production`
2. Fill in:
    - `APP_KEY`
    - DB passwords
    - SMTP credentials
    - `APP_IMAGE` / `APP_IMAGE_TAG`
    - `REVERB_APP_KEY` / `REVERB_APP_SECRET`
3. Point DNS `A` records for both domains to your VPS
4. Build locally or on the VPS with:
    - `docker compose -f docker-compose.prod.yml --env-file .env.production build`
5. Or push and pull a tagged image:
    - `docker push yourdockerhubname/asaba-hustle:latest`
    - `docker compose -f docker-compose.prod.yml --env-file .env.production pull`
6. Start the stack:
    - `docker compose -f docker-compose.prod.yml --env-file .env.production up -d`

Production stack:

- `app` runs the web app from the production image
- `queue` runs `queue:work`
- `reverb` runs websocket broadcasting
- `caddy` handles HTTPS and domain routing
- `mysql` and `redis` run as containers by default

Important production notes:

- `RUN_MIGRATIONS=true` should stay enabled for the first app boot, then you can set it to `false` on later rollouts if you prefer manual migrations
- the production image compiles frontend assets during the build
- local Docker files are still for development; deploy from `docker-compose.prod.yml`
- production mail must use real SMTP, not MailHog
- `mysql`, `redis`, and `reverb` should not be publicly exposed beyond the compose network

## Remaining Gaps

- broader automated coverage for negotiation and manual payment flows
- cleanup of stale legacy references in old commented UI/code paths
- more complete operational observability beyond the expanded activity log
- production hardening around queues, retries, and realtime infra
