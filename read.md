# Asaba Hustle Agent Handoff

This file summarizes the repository state as of March 19, 2026.

## High-Level Summary

Asaba Hustle is a Laravel 12 local-services marketplace with:

- an API-first backend
- a Blade web client enhanced with Tailwind CSS and jQuery AJAX
- an admin panel for moderation and review
- Laravel Reverb and Echo for realtime chat, notifications, and job-status updates
- legacy Paystack and Flutterwave gateway integrations at the payment-service layer

The project is no longer backend-only. The browser app is now a role-aware product surface for clients and workers, and the API has largely been kept in sync with the same job lifecycle.

## Current Stack

- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- Spatie Laravel Permission
- Laravel Reverb
- Blade
- Tailwind CSS v4
- jQuery
- Vite
- SQLite for tests/local fallback
- MySQL or MariaDB for normal deployment

## Current Product State

### API Layer

Implemented and routed:

- auth
- skills
- jobs
- messages
- notifications
- payments
- payment webhooks

The API response shape is standardized:

```json
{
    "success": true,
    "message": "...",
    "data": {},
    "meta": {}
}
```

Validation, auth, authorization, and not-found errors are normalized for API consumers in `bootstrap/app.php`.

The API now includes the current job lifecycle endpoints:

```text
POST /api/jobs/{job}/apply
POST /api/jobs/{job}/hire
POST /api/jobs/{job}/accept
POST /api/jobs/{job}/start
POST /api/jobs/{job}/complete
POST /api/jobs/{job}/mark-paid
POST /api/jobs/{job}/confirm-payment
POST /api/jobs/{job}/rate
```

`ServiceJobResource` now exposes `paid_at` and can include the related rating when loaded.

### Web Layer

Current browser routes/pages include:

```text
GET  /
GET  /login
POST /login
GET  /register
POST /register
GET  /verify-phone
POST /verify-phone
GET  /forgot-password
POST /forgot-password
GET  /reset-password
POST /reset-password
POST /logout

GET  /app
GET  /app/me
PUT  /app/profile
POST /app/change-password
POST /app/upload-id
POST /app/availability
POST /app/send-verification-token
POST /app/verify-contact

GET  /app/jobs
POST /app/jobs
GET  /app/jobs/{job}
POST /app/jobs/{job}/apply
POST /app/jobs/{job}/hire
POST /app/jobs/{job}/accept
POST /app/jobs/{job}/start
POST /app/jobs/{job}/complete
POST /app/jobs/{job}/mark-paid
POST /app/jobs/{job}/confirm-payment
POST /app/jobs/{job}/rate
GET  /app/jobs/{job}/suggested-workers
GET  /app/my-jobs

GET  /app/conversations
GET  /app/conversations/{conversation}/messages
POST /app/conversations/{conversation}/read
POST /app/messages

GET  /app/notifications
POST /app/notifications/read
POST /app/notifications/read-all
```

Admin routes:

```text
/admin/dashboard
/admin/users
/admin/jobs
/admin/payments
/admin/ratings
/admin/activity-logs
```

## What Exists Now

### Dashboard and Role-Aware Home

`/app` behaves differently by role:

- clients search and browse skills
- clicking a skill opens a modal to post a new job
- clients get a floating create-job button
- workers search open jobs related to their own skills
- dashboard shows recent chats instead of a static placeholder

The dashboard search is server-driven through `AppController@index()`.

### Profile and Identity

The profile page supports updating:

- name
- bio
- `primary_skill_id`
- additional worker skills
- `availability_status`
- `id_document`
- `latitude`
- `longitude`
- worker bank details:
  - `bank_name`
  - `account_name`
  - `account_number`

Location behavior:

- saved coordinates are prefilled automatically
- if missing, browser geolocation is used
- the user can manually refresh coordinates

Verification rules are now split:

- phone/email verification only confirms contact ownership
- `is_verified` is platform verification only
- new users stay `is_verified = 0`
- only admin can change `is_verified` to `1`
- admin approval requires an uploaded `id_document`

### Multi-Skill Workers

Workers no longer have only one skill.

Current model:

- `primary_skill_id` still exists for compatibility
- extra worker skills are stored through `skill_user`
- worker discovery and dashboard matching use both primary and attached skills

Migration added:

- `database/migrations/2026_03_18_210000_create_skill_user_table.php`

### Jobs, Hiring, Payment, and Rating Flow

Job detail is now much more complete:

- workers can apply through AJAX
- workers cannot chat until they apply
- after applying, workers can start or continue chat with the client
- clients see applications instead of a direct chat CTA
- clients can inspect an applicant before hiring
- clients can view assigned worker details after assignment
- clients can see worker account details when payment becomes relevant

Assigned job protections now exist:

- assigned jobs do not show apply UI again
- non-assigned workers cannot apply to already assigned jobs
- non-assigned workers cannot see the assigned worker identity

Current job lifecycle:

- `open`
- `assigned`
- `worker_accepted`
- `in_progress`
- `payment_pending`
- `completed`
- `rated`

Current job payment options:

- `cash`
- `transfer`

Current closeout flow:

- worker marks work completed
- job moves to `payment_pending`
- client marks payment as sent
- worker confirms payment receipt
- job moves to `completed`
- client rates the worker
- job moves to `rated`

The worker's stored `rating` value is now synchronized from the average of all received job ratings whenever a new rating is created.

### Messaging

Messaging is now a real inbox flow instead of a placeholder.

Implemented:

- conversation list page
- conversation thread loading
- AJAX send
- optimistic message append
- duplicate optimistic/realtime reconciliation
- unread badges in the conversation sidebar
- latest message preview updating in the sidebar
- active conversation click does not reopen/reload
- mobile off-canvas conversation list
- realtime chat via Echo/Reverb
- conversation ordering by latest message activity so newly active chats rise to the top

Conversation identifiers now use UUIDs instead of exposing only numeric IDs.

Relevant work includes:

- UUID route model binding for conversations
- private channel naming by conversation UUID
- message links that deep-link into a selected conversation

### Notifications

Navbar notifications are now functional:

- dropdown list in the navbar
- unread badge count
- fetch notifications from the web endpoint
- mark one as read
- mark all as read
- realtime updates via Echo on `private-user.{id}`
- job/chat notifications can now carry action links to the relevant job or conversation

Message and job flows now create notifications:

- new chat message -> receiver gets notification
- worker applies for a job -> client gets notification
- client hires a worker -> worker gets notification
- later payment/job status actions continue through the shared notification service where implemented

### Realtime / Broadcast State

Broadcasting is active in the codebase for:

- chat messages
- user notifications
- job status updates

Important channels:

- `private-conversation.{uuid}`
- `private-user.{id}`
- `private-job.{id}`

### Conversations

Conversation list and message loading now use:

- direct `conversation->client` and `conversation->worker`
- not `job->worker` as a fallback for pre-hire threads

This fixed the earlier "Deleted user" problem in the conversation list.

## Key Files Touched In Recent Progress

### Backend

- `app/Http/Controllers/Web/AppController.php`
- `app/Http/Controllers/Api/ServiceJobController.php`
- `app/Http/Controllers/Admin/AdminUserController.php`
- `app/Policies/ServiceJobPolicy.php`
- `app/Services/AuthSecurityService.php`
- `app/Services/JobService.php`
- `app/Services/ChatService.php`
- `app/Services/WorkerDiscoveryService.php`
- `app/Services/UserNotificationService.php`
- `app/Models/User.php`
- `app/Models/Skill.php`
- `app/Models/Conversation.php`
- `app/Events/JobStatusUpdated.php`
- `app/Events/ChatMessageBroadcasted.php`
- `app/Events/NotificationBroadcasted.php`
- `routes/channels.php`
- `routes/api.php`

### Requests / Resources

- `app/Http/Requests/UpdateProfileRequest.php`
- `app/Http/Requests/Admin/UpdateUserStatusRequest.php`
- `app/Http/Resources/UserResource.php`
- `app/Http/Resources/ServiceJobResource.php`

### Frontend / Views

- `resources/views/web/app.blade.php`
- `resources/views/web/profile.blade.php`
- `resources/views/web/job-detail.blade.php`
- `resources/views/web/messages.blade.php`
- `resources/views/partials/nav.blade.php`
- `resources/views/admin/users/show.blade.php`
- `resources/views/components/select.blade.php`
- `resources/js/app.js`
- `resources/js/main.js`

### Database

- `database/migrations/2026_03_18_190000_add_uuid_to_conversations_table.php`
- `database/migrations/2026_03_18_210000_create_skill_user_table.php`
- `database/migrations/2026_03_19_120000_update_service_job_payment_flow.php`
- `database/migrations/2026_03_19_130000_add_account_details_to_users_table.php`

## Frontend Architecture Direction

Current frontend pattern:

- Blade renders pages and modal shells
- `resources/js/main.js` contains reusable page logic
- `resources/js/app.js` boots shared functionality
- forms mutate state over AJAX
- realtime UI updates are handled through Echo listeners where relevant

This pattern is intentional and should be preserved unless there is a strong reason to change it.

## Operational Notes

Do not assume assets were production-built.

The user previously said they prefer to run `npm run dev` themselves.

That means:

- `npm install` has already been run
- `jquery` is installed
- do not claim `vite build` is verified unless you actually run it

Realtime features depend on the runtime environment being up:

- Vite/dev assets
- Reverb server
- queue worker where queued listeners are used

## Tests / Verification Notes

Recent verification done during implementation was mostly targeted:

- `php -l` on changed PHP files
- `node --check` on changed JS files
- `php artisan view:cache` after larger Blade changes
- targeted `php artisan test` runs for auth/admin changes

Not all work was followed by a full `php artisan test` pass after every change.

Earlier there were mismatches between older tests and the newer Blade/AJAX browser behavior, so do not assume the full test suite completely reflects the current UI contracts.

## Known Gaps / Remaining Work

Still incomplete or worth refining:

- polished public marketing/landing experience
- richer map/location display on job detail instead of placeholder map block
- end-to-end test coverage for realtime and notification flows
- better browser-side pagination/filter UX for larger job/message datasets
- deeper queue/listener audit
- real SMS integration
- production hardening around observability and retries

## Suggested Next Tasks

Pick one direction at a time:

1. Harden tests around current browser behavior and API parity
2. Improve payment/browser callback UX
3. Add richer worker public profile pages beyond the modal preview
4. Improve chat persistence/search and conversation ordering
5. Add stronger admin tools for verification review, disputes, and moderation

## One-Line Status

The repository now has a solid API, a working admin panel, a role-aware AJAX-driven Blade app, realtime chat and notifications, realtime job-status updates, multi-skill workers, admin-controlled verification, and a functional application-to-payment-to-rating job flow, but it still needs product polishing and broader automated coverage.
