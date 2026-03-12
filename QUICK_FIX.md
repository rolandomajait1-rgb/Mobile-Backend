# Quick Fix for Timeout Error

## Problem
Frontend is timing out because:
1. Backend URL might be wrong
2. Timeout was too short for Render free tier cold starts

## Solution

### Step 1: Get Your Backend URL from Render
1. Go to https://render.com/dashboard
2. Click on your backend service
3. Copy the URL (should be like: `https://amber-backend-xxxx.onrender.com`)

### Step 2: Update Frontend Environment Variable

Edit `frontend/.env.production`:
```env
VITE_API_BASE_URL=https://your-actual-backend-url.onrender.com
```

Replace with YOUR actual Render backend URL!

### Step 3: Commit and Redeploy

```bash
# Commit the timeout fix
git add frontend/src/utils/axiosConfig.js
git commit -m "Fix: Increase axios timeout to 90s for Render cold starts"
git push origin main

# Update .env.production with your backend URL
# Then commit
git add frontend/.env.production
git commit -m "Update backend URL to actual Render deployment"
git push origin main

# Redeploy frontend
cd frontend
vercel --prod
```

### Step 4: Test Backend Directly

Before redeploying, test if your backend is working:

```bash
# Replace with YOUR backend URL
curl https://your-backend.onrender.com/up

# Should return: {"status":"ok"}
```

If it takes 30+ seconds the first time, that's normal for Render free tier!

### Step 5: Wake Up Your Backend

Visit your backend URL in a browser first to wake it up:
- https://your-backend.onrender.com/up

Then refresh your frontend.

---

## Alternative: Keep Backend Awake

Render free tier sleeps after 15 min. Options:

1. **Upgrade to Starter ($7/mo)** - Always on, no cold starts
2. **Use a ping service** - Keep it awake (but uses your free hours)
3. **Accept cold starts** - First request takes 30-60s

---

## What I Fixed

✅ Increased axios timeout from 15s to 90s
- Now handles Render cold starts properly

Next: Update your backend URL in `.env.production`!
