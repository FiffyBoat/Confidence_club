# Render + Supabase Notes

This project is now set up to use Supabase Postgres as the primary database.

## Recommended deployment shape

- Deploy the app to Render as a Docker web service.
- Use Supabase for PostgreSQL.
- Keep `RUN_MIGRATIONS=true` for first deploys to a new database.
- Keep `RUN_SEEDERS=false` unless you intentionally want the default seeded accounts.

The included `render.yaml` already matches that setup and expects you to provide these Render environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-service>.onrender.com
APP_KEY=<your-app-key>
DB_CONNECTION=pgsql
DB_HOST=<your-supabase-session-pooler-host>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<your-supabase-pooler-username>
DB_PASSWORD=<your-supabase-db-password>
DB_SSLMODE=require
DB_SCHEMA=public
RUN_MIGRATIONS=true
RUN_SEEDERS=false
```

## Supabase connection recommendation

Use the Supabase `Session pooler` connection instead of the direct database host when possible.

Why:

- the direct host often depends on IPv6 support
- the pooler is usually easier to reach from local networks and hosted runtimes
- discrete env fields avoid connection-string parsing issues when passwords contain characters like `@`

## Data migration flow

If the Supabase database is brand new:

1. Deploy with `RUN_MIGRATIONS=true`.
2. After the service is healthy, import CSV snapshots if you want the existing repo data:

```bash
php artisan db:restore-csv-backup --dir=. --force
```

If the Supabase database already contains the correct data:

1. Deploy with `RUN_MIGRATIONS=true`.
2. Leave `RUN_SEEDERS=false`.
3. Do not run the CSV restore again unless you intentionally want to replace current database contents.

## Important storage warning

Supabase now stores the database, and this app can also store uploaded files in Supabase Storage through its S3-compatible endpoint.

To enable durable file storage on Render, set:

```env
CCM_FILES_DISK=supabase
SUPABASE_STORAGE_ACCESS_KEY_ID=<your-access-key>
SUPABASE_STORAGE_SECRET_ACCESS_KEY=<your-secret-key>
SUPABASE_STORAGE_BUCKET=<your-bucket-name>
SUPABASE_STORAGE_REGION=<your-storage-region>
SUPABASE_STORAGE_ENDPOINT=https://<your-project-ref>.supabase.co/storage/v1/s3
SUPABASE_STORAGE_PATH_STYLE=true
SUPABASE_STORAGE_VISIBILITY=private
```

These files will then use the managed disk instead of local-only storage:

- constitution uploads
- receipt PDFs
- any other generated files under `storage/app/public`

If you already have local files from earlier runs, migrate them to the configured managed disk with:

```bash
php artisan files:migrate-managed
```

Without that storage configuration, Render can still lose uploaded/generated files on redeploy or restart.

## Old SQLite tooling

The repo still contains legacy SQLite helper commands for local archive/recovery work, but they are no longer the primary deployment path.

Examples:

- `php artisan backup:sqlite`
- `php artisan db:archive`
- `php artisan db:switch full`

Those commands are guarded so they only run when the app is actually using SQLite.
