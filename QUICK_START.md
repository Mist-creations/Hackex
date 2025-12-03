# HACKEX - Quick Start Guide

## ⚡ 5-Minute Setup

### 1. Prerequisites Check
```bash
# Verify you have these installed:
php --version    # Should be 8.3+
composer --version
which nmap openssl curl unzip
```

### 2. Install & Configure
```bash
cd /Users/mac/Desktop/HackEx/hackex-app

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env and add your OpenAI API key:
# OPENAI_API_KEY=sk-your-key-here

# Run migrations
php artisan migrate
```

### 3. Start Application
```bash
# Terminal 1: Web Server
php artisan serve

# Terminal 2: Queue Worker
php artisan queue:work
```

### 4. Access Application
Open browser: **http://localhost:8000**

## 🎯 First Scan

### URL Scan:
1. Click "🌐 Scan URL" tab
2. Enter: `https://example.com`
3. Check consent checkbox
4. Click "Start Free Security Scan"
5. Wait 30-60 seconds for results

### ZIP Scan:
1. Click "📦 Upload ZIP" tab
2. Upload your project ZIP file
3. Check consent checkbox
4. Click "Start Free Security Scan"
5. Wait 30-60 seconds for results

## 📊 Understanding Results

### Security Score:
- **80-100:** ✅ Safe for Launch (green)
- **50-79:** ⚠️ Risky – Fix Recommended (yellow)
- **0-49:** ❌ Critical – Do Not Launch (red)

### Finding Severity:
- **🔴 Critical:** Fix immediately (exposed secrets, open databases)
- **🟠 High:** Fix before launch (missing headers, public admin)
- **🟡 Medium:** Fix soon (security best practices)
- **🔵 Low:** Nice to have (minor improvements)

### AI Explanations:
Click any finding to expand and see:
- 💡 What This Means (plain English)
- ⚠️ Real-World Attack Scenario
- ✅ How To Fix (step-by-step)

## 🔧 Common Issues

### "Queue jobs not processing"
```bash
# Make sure queue worker is running:
php artisan queue:work --tries=3
```

### "OpenAI API error"
```bash
# Check your API key in .env:
nano .env
# Verify: OPENAI_API_KEY=sk-...
```

### "nmap: command not found"
```bash
# macOS:
brew install nmap

# Ubuntu/Debian:
sudo apt-get install nmap
```

## 📁 Project Structure

```
hackex-app/
├── app/
│   ├── Http/Controllers/    # Web controllers
│   ├── Jobs/                # Queue jobs
│   ├── Models/              # Eloquent models
│   └── Services/            # Scanner services
├── database/
│   └── migrations/          # Database schema
├── resources/
│   └── views/               # Blade templates
├── routes/
│   └── web.php              # Web routes
└── storage/
    ├── app/                 # Uploaded files
    └── logs/                # Application logs
```

## 🎨 Theme Colors

- **Primary:** Sky Blue `#0EA5E9`
- **Secondary:** Black `#000000`
- **Neutral:** White `#FFFFFF`

## 📝 Key Files

- **Architecture:** `/architecture.md`
- **Full README:** `/hackex-app/README.md`
- **Setup Guide:** `/SETUP.md`
- **This Guide:** `/QUICK_START.md`

## 🚀 Production Deployment

### Before Going Live:
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Set up Supervisor for queue workers
6. Configure web server (Nginx/Apache)
7. Enable HTTPS/SSL
8. Set up monitoring

### Recommended Stack:
- **Web Server:** Nginx + PHP-FPM
- **Database:** PostgreSQL
- **Queue:** Redis + Supervisor
- **Caching:** Redis
- **SSL:** Let's Encrypt

## 📞 Need Help?

1. Check `SETUP.md` for detailed troubleshooting
2. Review `architecture.md` for technical details
3. Check Laravel logs: `storage/logs/laravel.log`
4. Enable debug mode: `APP_DEBUG=true` (temporarily)

## ✅ Verification Checklist

- [ ] PHP 8.3+ installed
- [ ] Composer dependencies installed
- [ ] `.env` file configured
- [ ] OpenAI API key added
- [ ] Database migrated
- [ ] Web server running (port 8000)
- [ ] Queue worker running
- [ ] Homepage loads at http://localhost:8000
- [ ] Can submit a scan
- [ ] Results display correctly

## 🎉 You're Ready!

If all checks pass, you're ready to start scanning projects!

**Next Steps:**
1. Scan your own project
2. Review the findings
3. Fix critical issues
4. Re-scan to verify fixes
5. Launch with confidence!

---

**HACKEX** - Don't launch blind. Scan fast. Launch safe.
