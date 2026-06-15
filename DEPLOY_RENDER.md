# Deploying to Render with a Free-Forever Database (Neon / Supabase)

Render's free database expires after **90 days**. To host your database **free forever**, you should use **Neon.tech** or **Supabase.com** (both offer free-forever PostgreSQL plans) and connect it to your Render web app.

---

## Step 1: Create Your Free Database (Choose One)

### Option A: Use Neon (Recommended & Easiest)
1. Go to **[Neon.tech](https://neon.tech/)** and sign up for a free account.
2. Create a new project named `vibe-chat`.
3. In your Neon Dashboard, copy the connection details. Click the **Connection Details** box and copy:
   * **Host**: (e.g., `ep-cool-snowflake-123456.us-east-2.aws.neon.tech`)
   * **Database**: `neondb` (or what you named it)
   * **Username**: `neondb_owner` (or similar)
   * **Password**: `YourNeonPassword`

### Option B: Use Supabase
1. Go to **[Supabase.com](https://supabase.com/)** and sign up for a free account.
2. Create a new project named `vibe-chat`.
3. Go to **Project Settings** -> **Database** and copy your connection info under **Connection Parameters**:
   * **Host**: `aws-0-us-east-1.pooler.supabase.com` (use the Transaction Pooler or Session Pooler host)
   * **Database**: `postgres`
   * **Username**: `postgres`
   * **Password**: `YourSupabasePassword`

---

## Step 2: Push the Code to GitHub
Ensure all configuration files (`Dockerfile`, `.dockerignore`, `render.yaml`) are committed and pushed to your GitHub repository:
```bash
git add .
git commit -m "Configure Render for external free-forever database"
git push origin main
```

---

## Step 3: Deploy on Render
1. Go to **[Render.com](https://dashboard.render.com/)**.
2. Click **New** (top-right) -> **Blueprint**.
3. Connect your GitHub repository.
4. Render will read the [render.yaml](file:///d:/VibeChat/Backend-Vibe-Chat/render.yaml) file.
5. In the **Environment Variables** configuration section that appears, Render will ask you to enter:
   * `DB_HOST`: Paste your Neon or Supabase Host
   * `DB_DATABASE`: Paste your Database name (`neondb` or `postgres`)
   * `DB_USERNAME`: Paste your Username
   * `DB_PASSWORD`: Paste your Database password
6. Click **Apply**. Render will build and deploy your app.

---

## Step 4: Run Database Migrations on Render
Once the deployment is complete, visit the secure migration route in your browser to build the database tables:
👉 **`https://your-render-url.onrender.com/api/deploy/migrate?key=deploy123`**

---

## Step 5: Storage Link (For media and voice uploads)
To make your uploads work:
👉 Visit: **`https://your-render-url.onrender.com/api/deploy/storage-link?key=deploy123`**

