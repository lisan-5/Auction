## Art Auction API (Laravel 12)

API-first art auction platform. Users register via email verification, artists list auctions with images, bidders place bids, and payments are processed via Chapa. Fully documented with Swagger and built for queued notifications.

## Highlights

- Email-first registration with verification code and auto-login (Sanctum tokens)
- Auctions: public browse; artists can create/update/delete their own
- Bidding with validation and real-time notification events
- Payments via Chapa (initiate, webhook verify, status lookup)
- Notifications: queued email (SMTP) + database notifications
- Swagger/OpenAPI docs at /api/documentation
- Roles/permissions (spatie/permission) and media uploads (spatie/medialibrary)

## Stack

- Laravel 12, PHP 8.2+
- Sanctum (API tokens)
- spatie/laravel-permission, spatie/laravel-medialibrary
- L5-Swagger for API docs
- SQLite by default (can use MySQL/PostgreSQL)

## Getting Started

1) Prerequisites
- PHP 8.2+, Composer
- Node.js (optional, for assets)

2) Setup
- Copy .env.example to .env and set your environment (DB, Mail, Chapa)
- Install deps: composer install
- Generate app key: php artisan key:generate
- Ensure a database exists
- Migrate: php artisan migrate
- Seed sample data: php artisan db:seed
- Link storage for media: php artisan storage:link
- Generate API docs (optional): php artisan l5-swagger:generate

3) Run
- Serve: php artisan serve
- Queues (prod recommended): php artisan queue:work --queue=mail,notifications

## Environment

App
- APP_URL: e.g. http://localhost:8000

Database 
- DB_CONNECTION=pgsql
- DB_DATABASE=database/database.pgsql


Queues
- Local: QUEUE_CONNECTION=sync (simple)
- Production: QUEUE_CONNECTION=database and run a worker

Chapa
- CHAPA_BASE_URL=https://api.chapa.co
- CHAPA_SECRET_KEY=your-secret

## API Overview (v1)

Auth & Verification
- POST /api/v1/auth/verify/request — request email code and magic link (begin registration)
- GET /api/v1/auth/verify/magic/{token} — register instantly via magic link (name & password in query)
- POST /api/v1/auth/verify/complete — verify code and create account (returns token)
- POST /api/v1/auth/verify/resend — resend code and magic link (throttled)
- POST /api/v1/login — login with email/password (returns token)
- GET /api/v1/me — current user (Bearer token)
- POST /api/v1/logout — revoke current token

- POST /api/v1/register — create account without sending email verification, magic links, or welcome emails (returns token on success)

Note: The primary registration endpoint POST /api/v1/register now creates a user without sending email verification, magic links, or welcome emails. It will return 201 with a token on success, or 409 with { code: "email_exists" } if the email is already registered.

If you need the original email-first registration flow (send verification code + magic link), use the verification endpoints under /api/v1/auth/verify (request -> complete) which still implement email-based registration.

Forgot Password
- POST /api/v1/password/forgot — request password reset link
- POST /api/v1/password/reset — reset password using token

Auctions
- GET /api/v1/auctions — list (public)
- GET /api/v1/auctions/{id} — show (public)
- POST /api/v1/auctions — create (auth; artist)
- PUT /api/v1/auctions/{id} — update (auth; owner)
- DELETE /api/v1/auctions/{id} — delete (auth; owner)

Bids
- POST /api/v1/auctions/{id}/bids — place a bid (auth)
Notifications
- GET /api/v1/notifications — list current user notifications (auth, paginated)

Payments (Chapa)
- POST /api/v1/payments — initiate payment (auth)
- GET /api/v1/payments/{tx_ref} — check status (auth or owner)
- POST /api/v1/payments/webhook — Chapa callback (public)

Admin (example)
- GET /api/v1/admin/test — protected by role:admin

Refer to Swagger docs for full request/response details.

## Swagger Docs

- UI: /api/documentation
- Regenerate: php artisan l5-swagger:generate

The OpenAPI annotations are in `app/Swagger/Endpoints/AuthEndpoints.php`. The `/api/v1/register` endpoint is documented to describe the no-email behavior.

## Notifications & Queues

- All email and database notifications are queued (ShouldQueue) by default.
- For local simplicity, you can use sync; for production, use database queues and a worker.
- Jobs/failed jobs tables are included; to create the failed jobs table, run:
	- php artisan queue:failed-table; php artisan migrate

## Roles & Permissions

- spatie/permission is configured; seeders provision roles (e.g., admin) and demo users.
- Some routes are gated by role middleware (like role:admin).

## Media & Storage

- Auction images are handled by spatie/medialibrary on the public disk.
- Ensure you run php artisan storage:link so images are web-accessible.
- Upload field name: images (supports multiple files).

## Testing

- Run tests: php artisan test