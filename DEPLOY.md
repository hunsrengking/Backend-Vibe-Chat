Preparing the Laravel app for cPanel (PostgreSQL)

Prerequisites on cPanel
- PHP 8.2+ with `pdo_pgsql` enabled
- PostgreSQL support (PostgreSQL Databases available)
- Ability to run Composer (preferred) or upload `vendor/` from local

Steps

1. Create PostgreSQL database and user in cPanel
   - Open "PostgreSQL Databases" and create a new database (e.g. `vibechat_db`).
   - Create a DB user and assign a strong password.
   - Add the user to the database with appropriate privileges.

2. Upload project files
   - Upload the repository to your cPanel account (outside `public_html`).
   - Ideally set the document root to the `public/` directory of the project. If you cannot change document root, copy contents of `public/` into `public_html/` and adjust `index.php` paths accordingly.

3. Configure environment
   - Copy `.env.example` to `.env` and edit DB settings to match cPanel values:
     - `DB_CONNECTION=pgsql`
     - `DB_HOST` (often `localhost`)
     - `DB_PORT=5432`
     - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
     - `DB_SSLMODE=require` (optional)

4. Install dependencies
   - If composer is available in cPanel terminal, run:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

   - If composer is not available, run `composer install` locally and upload the `vendor/` folder.

5. Permissions and storage

```bash
chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

6. Run migrations

```bash
php artisan migrate --force
php artisan db:seed --force   # optional
```

7. Background processes and scheduling
- Setup a cron job to run Laravel scheduler every minute:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

- For queue workers, prefer supervisor if available; otherwise use `php artisan queue:work` via a background process.

8. Troubleshooting
- If you see PDO errors, confirm `pdo_pgsql` is enabled in PHP info and `composer.json` includes `ext-pdo_pgsql` requirement.
- Ensure the `public/` folder is the web root or that `index.php` paths were adjusted.

Notes
- We added `ext-pdo_pgsql` to `composer.json` to make it explicit that the platform needs the PostgreSQL PDO extension.
- `config/database.php` default connection was set to `pgsql` to match PostgreSQL hosting.

If you want, I can also:
- Add a `public_html/.htaccess` snippet to help route requests, or
- Prepare a simple deploy script for cPanel file manager uploads.
