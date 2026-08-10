<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Confidence Club Members

This project is now set up to use PostgreSQL by default, which makes it a good fit for Supabase.

## Supabase Setup

1. Create a Supabase project and copy the `Session pooler` connection details from the Supabase dashboard.
2. Update `.env` with either:

```env
DB_CONNECTION=pgsql
DB_URL=postgresql://postgres.[YOUR-PROJECT-REF]:[URL-ENCODED-PASSWORD]@aws-1-[YOUR-REGION].pooler.supabase.com:5432/postgres?sslmode=require
DB_SSLMODE=require
DB_SCHEMA=public
```

3. Or set the discrete fields instead:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-1-[YOUR-REGION].pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.[YOUR-PROJECT-REF]
DB_PASSWORD=your-supabase-db-password
DB_SSLMODE=require
DB_SCHEMA=public
```

4. Run the schema on Supabase:

```bash
php artisan migrate
```

If you intentionally want the default seeded accounts on a brand-new database, run:

```bash
php artisan db:seed
```

5. If you want to move the existing CSV snapshots into Supabase after migrating:

```bash
php artisan db:restore-csv-backup --dir=. --force
```

The legacy SQLite backup commands are still available for old local database files, but the default app path is now Supabase/Postgres.

## Managed File Storage

The app can now store constitution uploads and receipt PDFs on a managed filesystem disk instead of the local server.

For local development, the default remains:

```env
CCM_FILES_DISK=public
```

For durable storage with Supabase Storage's S3-compatible endpoint, set:

```env
CCM_FILES_DISK=supabase
SUPABASE_STORAGE_ACCESS_KEY_ID=your-access-key
SUPABASE_STORAGE_SECRET_ACCESS_KEY=your-secret-key
SUPABASE_STORAGE_BUCKET=club-files
SUPABASE_STORAGE_REGION=project-region
SUPABASE_STORAGE_ENDPOINT=https://your-project-ref.supabase.co/storage/v1/s3
SUPABASE_STORAGE_PATH_STYLE=true
SUPABASE_STORAGE_VISIBILITY=private
```

If you already have constitution or receipt files in local `storage/app/public`, copy them to the configured managed disk with:

```bash
php artisan files:migrate-managed
```

Use `--dry-run` first if you want to preview the copy:

```bash
php artisan files:migrate-managed --dry-run
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
