# Render Rehost Notes

This project is already configured to deploy to Render as a Docker web service.

## What matters for this repo

- `render.yaml` defines a Docker web service.
- The container listens on port `10000`.
- The app currently uses SQLite by default.
- This repo includes tracked SQLite files at the project root:
  - `ccm_db`
  - `ccm_db_empty`
  - `ccm_db_source_copy`
- The local `.env` currently points `DB_DATABASE` to `ccm_db_empty`.
- User-generated files are written under `storage/app/public`.

## Important warning

If you deploy this repo to a brand-new Render service without changing storage strategy:

- the code will deploy,
- the tracked SQLite file bundled in the image can be used for the initial boot,
- but any runtime file changes will be lost on the next redeploy or restart unless you use a persistent disk or an external database.

That affects:

- SQLite data stored inside the container
- uploaded/generated files under `storage`

## Recommended Render setup

### Best paid option

Use a paid Render web service with a persistent disk.

1. Create the new service from this repo.
2. Attach a persistent disk mounted at:

```text
/var/www/html/storage
```

3. Set these environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-service>.onrender.com
APP_KEY=<copy the exact APP_KEY from the old Render service>
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/storage/app/ccm_db.sqlite
SQLITE_TEMPLATE_PATH=/var/www/html/ccm_db_empty
RUN_MIGRATIONS=false
```

4. Deploy.

With the current startup script, if `/var/www/html/storage/app/ccm_db.sqlite` does not exist yet, the container will initialize it from `ccm_db_empty` automatically.

5. If you want the richer existing dataset instead of the empty starter DB, replace the file once from the Render shell:

```sh
cp /var/www/html/ccm_db /var/www/html/storage/app/ccm_db.sqlite
```

6. If the old service has live runtime data that is newer than what was pushed to Git, copy the old SQLite file and any files under `storage/app/public` from the old service before switching traffic.

This is the best balance for this repo because it keeps your current SQLite-based app structure, avoids a bigger database migration right now, and makes data survive redeploys.

### Best free option

If you want to stay fully on Render's free tier, the best route for this repo is:

- free web service
- free Render Postgres
- no SQLite in production

This is better than free SQLite because Render's free web services lose local files whenever the service redeploys, restarts, or spins down.

The repo's `render.yaml` is now prepared for that flow:

- it creates a free Postgres database named `confidence-club-db`
- it configures the web service to use `DB_CONNECTION=pgsql`
- it pulls `DB_URL` from the database connection string
- it runs migrations on boot
- it runs seeders on boot

During Blueprint creation in Render, provide values for:

```env
APP_KEY=<copy the exact APP_KEY from the old service, or generate one locally with php artisan key:generate --show>
APP_URL=https://<your-service>.onrender.com
```

Free-plan caveats from Render:

- free web services spin down after 15 minutes idle
- free web services have no persistent disk
- free web services have no shell access or one-off jobs
- free Postgres expires 30 days after creation unless upgraded
- free Postgres has no backups

So this is the best free Render setup for testing or short-term hosting, but not a durable production setup.

### Option A: Quickest rehost

Use this if you only want the pushed version online quickly.

1. Create a new Render web service from the same Git repo.
2. Choose `Docker` as the runtime, or deploy from the repo Blueprint.
3. Set these environment variables in Render:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-service>.onrender.com
APP_KEY=<copy from current Render service or current working .env>
DB_CONNECTION=sqlite
DB_DATABASE=ccm_db_empty
RUN_MIGRATIONS=false
```

This works, but the SQLite database lives in the container filesystem and is not durable.

### Option B: Long-term upgrade

Move from SQLite to Render Postgres when you want stronger durability, backups, and less filesystem coupling. That is the better architecture long-term, but it is a larger migration than you need for a clean account-to-account rehost.

## If you are moving live data from another Render account

Do not rely on the Git repo alone unless you are sure the latest data was committed into one of the tracked SQLite files.

Before switching accounts, confirm all of the following from the old Render service:

- the exact `APP_KEY`
- the actual SQLite file in use
- any files under `storage/app/public`

If the old service has newer runtime data than the repo, export or copy that data first, then place it into the new service.

## Notes about migrations

- `docker/start.sh` only runs migrations when `RUN_MIGRATIONS=true`.
- For a copied SQLite database, leave `RUN_MIGRATIONS=false`.
- For a fresh empty database, you can temporarily set `RUN_MIGRATIONS=true`, then run seeders manually if needed.

## Seeded default accounts

If you build a fresh database and run migrations + seeders, the default seeded users are:

- `admin@example.com` / `admin12345`
- `treasurer@example.com` / `treasurer12345`
- `viewer@example.com` / `viewer12345`
