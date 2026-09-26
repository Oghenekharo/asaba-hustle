# Production Docker deployment

1. Copy `.env.production.example` to `.env.production` and fill in unique secrets, the public site URL, mail/SMS credentials, Reverb settings, and the initial admin credentials. Generate `APP_KEY` with `php artisan key:generate --show`; do not change it after users have signed in.
2. Build and start the production stack:

   ```sh
   docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
   ```

   The app container runs only forward migrations. It never runs seeders or `migrate:fresh`.
3. Seed the production baseline once, after the database is available:

   ```sh
   docker compose --env-file .env.production -f docker-compose.prod.yml run --rm seed-production
   ```

   This creates baseline roles and skills and the configured admin if that phone number does not already exist. It does not create demo users or jobs. Existing admin profile/password data is preserved on reruns.
4. For deployments that require migrations to be a separate release step, run `migrate` before starting/recreating the app, and set `RUN_MIGRATIONS=false` in the deployment environment:

   ```sh
   docker compose --env-file .env.production -f docker-compose.prod.yml run --rm migrate
   ```

The app binds to loopback ports 8000 and 8080 for a host reverse proxy. Configure TLS and WebSocket forwarding for Reverb at the proxy. Keep `.env.production` out of source control and image builds.
