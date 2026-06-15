# Deployment Guide for wupai.smartdigitalhr.com on cPanel

This guide is customized for your actual cPanel structure:
* **cPanel Username**: `vyxxuozh`
* **Project Location**: `/home/vyxxuozh/wupai.smartdigitalhr.com/Backend-Vibe-Chat`

---

## 1. Domain Routing & Document Root (Choose ONE option)

Since your project is uploaded inside a subdirectory (`Backend-Vibe-Chat`), you need to make sure visits to `https://wupai.smartdigitalhr.com` route directly to the Laravel `public` directory.

### Option A: Change cPanel Document Root (Highly Recommended & Cleanest)
If cPanel allows you to change the Document Root for `wupai.smartdigitalhr.com` (often editable in the **Domains** section of cPanel by clicking the **Manage** button next to the domain):
* Change it from `/wupai.smartdigitalhr.com` to:
  ```
  /wupai.smartdigitalhr.com/Backend-Vibe-Chat/public
  ```
* If you do this, you do **not** need any root-level `.htaccess` redirects.

### Option B: Use `.htaccess` Redirect (If you cannot change Document Root)
If cPanel does not let you change the Document Root, you should place a `.htaccess` file directly inside `/home/vyxxuozh/wupai.smartdigitalhr.com/` (one level **above** `Backend-Vibe-Chat`) with the following content:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/Backend-Vibe-Chat/public/
    RewriteRule ^(.*)$ Backend-Vibe-Chat/public/$1 [L]
</IfModule>
```

---

## 2. Environment Configuration (`.env`)

In your cPanel File Manager, edit the `.env` file at `/home/vyxxuozh/wupai.smartdigitalhr.com/Backend-Vibe-Chat/.env` and verify the values:

```ini
APP_NAME=VibeChat
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wupai.smartdigitalhr.com

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=vyxxuozh_vibe_chat_db
DB_USERNAME=vyxxuozh_tufu
DB_PASSWORD=Tufu505535%$#
DB_SSLMODE=require                    # Or 'prefer' depending on cPanel PostgreSQL settings
```

---

## 3. Deployment Commands

Since your cPanel terminal is currently hitting resource limits (**CageFS / LVE limits: Unable to fork**), you can execute the setup commands directly through your web browser using secure helper routes.

### Method A: Using Web Browser (Recommended when Terminal is broken)
Ensure your `.env` file is fully configured, then visit these URLs in your web browser:

1. **Run Database Migrations**:
   * Visit: `https://wupai.smartdigitalhr.com/deploy/migrate?key=deploy123`
2. **Create the Storage Link** (makes uploaded media/voice notes work):
   * Visit: `https://wupai.smartdigitalhr.com/deploy/storage-link?key=deploy123`
3. **Optimize & Cache Configuration**:
   * Visit: `https://wupai.smartdigitalhr.com/deploy/cache?key=deploy123`
4. **Clear Caches** (if you need to force a configuration reload later):
   * Visit: `https://wupai.smartdigitalhr.com/deploy/clear?key=deploy123`

---

### Method B: Using SSH/Terminal (If resource limits are fixed)
If your terminal starts working again, run these inside `/home/vyxxuozh/wupai.smartdigitalhr.com/Backend-Vibe-Chat`:

* **Install dependencies**: `composer install --no-dev --optimize-autoloader`
* **Generate key**: `php artisan key:generate`
* **Run migrations**: `php artisan migrate --force`
* **Storage link**: `php artisan storage:link`
* **Cache**: `php artisan config:cache && php artisan route:cache && php artisan view:cache`


---

## 4. Setup Cron Job for Laravel Scheduler

To keep background tasks running (like pruning expired tokens, queues, etc.), set up a Cron Job in your cPanel dashboard:

1. Search for **Cron Jobs** in cPanel.
2. Select **Once Per Minute** (`* * * * *`) for the interval.
3. Enter the following command:
   ```bash
   cd /home/vyxxuozh/wupai.smartdigitalhr.com/Backend-Vibe-Chat && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## 5. Troubleshooting & SSL

* **HTTPS Redirects**: Your cPanel shows **Force HTTPS Redirect** is **On**. Make sure your `APP_URL` starts with `https://`.
* **Database Connection Issues**: Double check that you've assigned the PostgreSQL database user `vyxxuozh_tufu` to the database `vyxxuozh_vibe_chat_db` in the cPanel PostgreSQL Database wizard with all privileges.
