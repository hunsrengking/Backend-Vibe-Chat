# Deploying to Render (Laravel App + PostgreSQL Database)

This guide shows you how to deploy both your Laravel backend and a free PostgreSQL database on Render.com using the blueprint configuration I've created.

---

## Step 1: Push the Project to GitHub
Make sure all the configuration files (`Dockerfile`, `.dockerignore`, `render.yaml`) are committed and pushed to your GitHub repository:
```bash
git add .
git commit -m "Configure Render blueprint with free PostgreSQL"
git push origin main
```

---

## Step 2: Deploy on Render
1. Create or log into your account at **[Render.com](https://dashboard.render.com/)**.
2. Click the **New** button (top-right) and select **Blueprint**.
3. Connect your GitHub repository.
4. Render will read [render.yaml](file:///d:/VibeChat/Backend-Vibe-Chat/render.yaml) and show you two services it plans to create:
   * **vibe-chat-db** (PostgreSQL database - Free plan)
   * **vibe-chat-backend** (Docker Web Service - Free plan)
5. Click **Apply**.
6. Render will now provision the database and automatically build/deploy your Laravel application.

---

## Step 3: Run Database Migrations on Render
Once the deployment is complete, your Laravel application needs to create the database tables.

### Option A: Via Web Browser (easiest)
Simply visit this URL in your web browser (replace `vibe-chat-backend.onrender.com` with your actual Render URL):
👉 **`https://vibe-chat-backend.onrender.com/deploy/migrate?key=deploy123`**

### Option B: Via Render Console Shell
1. Go to your **vibe-chat-backend** web service on the Render dashboard.
2. In the left menu, click **Shell**.
3. Run this command:
   ```bash
   php artisan migrate --force
   ```

---

## Step 4: Storage Link (For media and voice uploads)
To ensure voice and video uploads are fully accessible:
👉 Visit: **`https://vibe-chat-backend.onrender.com/deploy/storage-link?key=deploy123`**
*(Or run `php artisan storage:link` inside the Render Shell).*
