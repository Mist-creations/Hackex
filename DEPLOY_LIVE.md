# 🚀 DEPLOY HACKEX LIVE - QUICK GUIDE

## ⚡ **FASTEST: Railway.app (5 minutes)**

### **Steps:**

1. **Go to Railway.app**
   ```
   https://railway.app
   ```

2. **Sign up with GitHub** (free tier available)

3. **Click "New Project" → "Deploy from GitHub repo"**

4. **Select your repository:**
   ```
   Mist-creations/Hackex
   ```

5. **Railway will auto-detect the Dockerfile and deploy!**

6. **Get your live URL:**
   ```
   https://your-app.railway.app
   ```

**That's it! Your app will be live in 5 minutes!** ✅

---

## 🔵 **Option 2: Render.com (Free Tier)**

### **Steps:**

1. **Go to Render.com**
   ```
   https://render.com
   ```

2. **Sign up with GitHub**

3. **Click "New +" → "Web Service"**

4. **Connect your GitHub repo:**
   ```
   Mist-creations/Hackex
   ```

5. **Configure:**
   - **Name:** hackex
   - **Root Directory:** `hackex-app`
   - **Environment:** Docker
   - **Dockerfile Path:** `Dockerfile`
   - **Plan:** Free

6. **Click "Create Web Service"**

7. **Your live URL:**
   ```
   https://hackex.onrender.com
   ```

---

## 🟣 **Option 3: Heroku (Classic)**

### **Steps:**

1. **Install Heroku CLI** (if not installed)
   ```bash
   brew tap heroku/brew && brew install heroku
   ```

2. **Login to Heroku**
   ```bash
   heroku login
   ```

3. **Create app**
   ```bash
   cd /Users/mac/Desktop/HackEx/hackex-app
   heroku create your-hackex-app
   ```

4. **Deploy**
   ```bash
   heroku container:push web
   heroku container:release web
   ```

5. **Open your app**
   ```bash
   heroku open
   ```

---

## 🟢 **Option 4: Vercel (Serverless)**

Vercel doesn't support Docker well, but we can use their PHP runtime:

1. **Go to Vercel.com**
   ```
   https://vercel.com
   ```

2. **Import your GitHub repo**

3. **Configure:**
   - **Framework Preset:** Other
   - **Root Directory:** `hackex-app`
   - **Build Command:** `composer install`
   - **Output Directory:** `public`

4. **Deploy!**

---

## 🔴 **Option 5: DigitalOcean App Platform**

1. **Go to DigitalOcean**
   ```
   https://cloud.digitalocean.com/apps
   ```

2. **Create App → GitHub**

3. **Select repo:** `Mist-creations/Hackex`

4. **Configure:**
   - **Type:** Web Service
   - **Dockerfile Path:** `hackex-app/Dockerfile`
   - **HTTP Port:** 80

5. **Deploy!**

---

## ⚡ **RECOMMENDED: Railway.app**

**Why Railway?**
- ✅ Free tier (500 hours/month)
- ✅ Auto-deploys from GitHub
- ✅ Custom domains
- ✅ Environment variables
- ✅ Logs and metrics
- ✅ Zero configuration

**Live in 5 minutes!**

---

## 🎯 **QUICK DEPLOY COMMANDS**

### **Railway (Fastest)**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Deploy
cd /Users/mac/Desktop/HackEx
railway init
railway up
```

### **Heroku**
```bash
cd /Users/mac/Desktop/HackEx/hackex-app
heroku create
heroku container:push web
heroku container:release web
heroku open
```

---

## 📝 **ENVIRONMENT VARIABLES TO SET**

For any platform, set these:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-key-here
APP_URL=https://your-domain.com

DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

---

## 🔧 **POST-DEPLOYMENT**

After deploying, run migrations:

**Railway:**
```bash
railway run php artisan migrate --force
```

**Heroku:**
```bash
heroku run php artisan migrate --force
```

---

## 🎬 **DEMO READY**

Once deployed, you'll have a live link like:
- `https://hackex.railway.app`
- `https://hackex.onrender.com`
- `https://your-hackex-app.herokuapp.com`

**Share this link for your hackathon demo!** 🏆

---

## ⏱️ **TIME ESTIMATES**

- **Railway:** 5 minutes ⚡
- **Render:** 10 minutes
- **Heroku:** 15 minutes
- **DigitalOcean:** 10 minutes
- **Vercel:** 20 minutes (needs config)

---

**Go with Railway for the fastest deployment!** 🚀
