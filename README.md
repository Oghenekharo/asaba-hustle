# Asaba Hustle

Asaba Hustle is a marketplace for local service jobs built with Laravel/JQuery/TailwindCSS. Clients post jobs, workers apply or negotiate, one worker gets assigned, the job moves through completion and payment confirmation, and both sides can rate each other at the end.

## What It Includes

- Blade web app for clients and workers
- JSON API for mobile or external clients
- Admin panel
- Realtime chat, notifications, and job updates
- PWA support with push notifications and offline fallback
- Manual payment flow for `cash` and `transfer`
- Transfer receipt upload and worker receipt review
- Two-sided rating after job closeout

## Core Job Flow

1. Client posts a job.
2. Workers apply or negotiate with an amount and message.
3. Client accepts one worker.
4. Assigned worker accepts, starts, and completes the job.
5. Client marks payment sent.
6. For transfer jobs, client uploads a receipt and worker reviews it.
7. Worker confirms payment.
8. Both sides can rate each other.

Main statuses:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`
- `cancelled`

## Important Behavior

- Only verified workers can apply.
- Jobs stop being publicly viewable once assigned; only participants and admins can open job details.
- Route maps can be shown during active coordination, but location is hidden after completion for safety.
- Once one worker is accepted, the client no longer sees location data for unaccepted workers.

## Local Setup

### Docker

Use the local Docker stack with:

```bash
docker compose --env-file .env.docker --profile local up --build
```

If you also want realtime services:

```bash
docker compose --env-file .env.docker --profile local --profile realtime up --build
```

Main local services:

- app: `http://127.0.0.1:8000`
- vite: `http://127.0.0.1:5173`
- mailhog UI: `http://127.0.0.1:8025`
- mysql
- redis
- queue
- optional reverb

### Non-Docker

Standard Laravel flow:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Environment Files

The repo-local env files in active use are:

- `.env`
- `.env.example`
- `.env.docker`
- `.env.production`

## Main Areas

- Web jobs: `app/Http/Controllers/Web/JobController.php`
- API jobs: `app/Http/Controllers/Api/ServiceJobController.php`
- Job workflow logic: `app/Services/JobService.php`
- Notifications: `app/Services/UserNotificationService.php`
- Job policy rules: `app/Policies/ServiceJobPolicy.php`
- Web UI logic: `resources/js/main.js`

## Verification

Typical checks:

- `php -l` on changed PHP files
- `php artisan route:list` when routes change
- `npm run build` for frontend changes
