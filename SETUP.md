# HACKEX - Complete Setup Guide

## 📋 Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Steps](#installation-steps)
3. [Configuration](#configuration)
4. [Running the Application](#running-the-application)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)

## 🖥️ System Requirements

### Required Software
- **PHP:** 8.3 or higher
- **Composer:** Latest version
- **Database:** SQLite (included) or PostgreSQL
- **Node.js:** 18+ (optional, for asset compilation)

### Required Server Tools
These command-line tools must be installed and accessible:

```bash
# Check if tools are installed
which nmap      # Port scanning
which openssl   # SSL certificate validation
which curl      # HTTP requests
which unzip     # ZIP file extraction
```

#### Installing Missing Tools

**macOS (using Homebrew):**
```bash
brew install nmap openssl curl unzip
```

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install nmap openssl curl unzip
```

**CentOS/RHEL:**
```bash
sudo yum install nmap openssl curl unzip
```

### OpenAI API Key
You'll need an OpenAI API key for AI-powered explanations:
1. Sign up at https://platform.openai.com
2. Navigate to API Keys section
3. Create a new API key
4. Save it securely (you'll need it during configuration)

## 🚀 Installation Steps

### Step 1: Navigate to Project Directory
```bash
cd /Users/mac/Desktop/HackEx/hackex-app
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

This will install all Laravel dependencies including:
- Laravel Framework 11.x
- Laravel Sanctum (authentication)
- Guzzle HTTP client
- And other required packages

### Step 3: Environment Configuration
```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Configure Environment Variables
Edit the `.env` file with your preferred text editor:

```bash
nano .env
# or
vim .env
# or
code .env  # VS Code
```

**Required Configuration:**

```env
# Application
APP_NAME=HACKEX
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite is pre-configured)
DB_CONNECTION=sqlite

# Queue (for async scanning)
QUEUE_CONNECTION=database

# OpenAI Configuration (REQUIRED)
OPENAI_API_KEY=sk-your-actual-api-key-here
OPENAI_MODEL=gpt-4

# Scan Configuration
SCAN_MAX_FILE_SIZE=52428800  # 50MB
SCAN_RATE_LIMIT=5
```

**Important:** Replace `sk-your-actual-api-key-here` with your real OpenAI API key.

### Step 5: Database Setup
```bash
# Run migrations to create database tables
php artisan migrate
```

This creates:
- `users` table
- `scans` table
- `findings` table
- `cache` table
- `jobs` table (for queue)

### Step 6: Storage Permissions
```bash
# Ensure storage directories are writable
chmod -R 775 storage bootstrap/cache
```

## ⚙️ Configuration

### OpenAI API Configuration

**Using GPT-4 (Recommended):**
```env
OPENAI_API_KEY=sk-your-key
OPENAI_MODEL=gpt-4
```

**Using GPT-3.5 (Faster, cheaper):**
```env
OPENAI_API_KEY=sk-your-key
OPENAI_MODEL=gpt-3.5-turbo
```

**Using Claude (Alternative):**
```env
OPENAI_API_KEY=your-anthropic-key
OPENAI_API_URL=https://api.anthropic.com/v1/messages
OPENAI_MODEL=claude-3-sonnet-20240229
```

### Scan Configuration

**Adjust file size limits:**
```env
SCAN_MAX_FILE_SIZE=104857600  # 100MB
```

**Adjust rate limiting:**
```env
SCAN_RATE_LIMIT=10  # 10 scans per hour
```

### Database Configuration (PostgreSQL)

If you prefer PostgreSQL over SQLite:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hackex
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Then run migrations:
```bash
php artisan migrate:fresh
```

## 🏃 Running the Application

### Development Mode

**Terminal 1: Start Web Server**
```bash
php artisan serve
```

The application will be available at: http://localhost:8000

**Terminal 2: Start Queue Worker**
```bash
php artisan queue:work --tries=3
```

The queue worker processes scans asynchronously.

### Production Mode

**Using Laravel Octane (High Performance):**
```bash
composer require laravel/octane
php artisan octane:install --server=swoole
php artisan octane:start --port=8000
```

**Using Supervisor (Queue Management):**

Create `/etc/supervisor/conf.d/hackex-worker.conf`:
```ini
[program:hackex-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/hackex-app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/hackex-app/storage/logs/worker.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hackex-worker:*
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Test Coverage
```bash
php artisan test --coverage
```

### Manual Testing Checklist

1. **Homepage Access:**
   - Visit http://localhost:8000
   - Verify landing page loads with sky blue theme

2. **URL Scan:**
   - Enter a test URL (e.g., https://example.com)
   - Check consent checkbox
   - Submit scan
   - Verify scan progress page appears
   - Wait for results

3. **ZIP Scan:**
   - Create a test ZIP with a .env file
   - Upload ZIP file
   - Check consent checkbox
   - Submit scan
   - Verify findings are detected

4. **Results Page:**
   - Verify security score displays
   - Check verdict badge color
   - Expand finding details
   - Verify AI explanations appear

## 🔧 Troubleshooting

### Issue: "Class 'ZipArchive' not found"

**Solution:**
```bash
# macOS
brew install php-zip

# Ubuntu/Debian
sudo apt-get install php-zip

# CentOS/RHEL
sudo yum install php-zip
```

### Issue: "nmap: command not found"

**Solution:**
```bash
# macOS
brew install nmap

# Ubuntu/Debian
sudo apt-get install nmap

# CentOS/RHEL
sudo yum install nmap
```

### Issue: "OpenAI API error: 401 Unauthorized"

**Solution:**
- Verify your API key is correct in `.env`
- Check if your OpenAI account has credits
- Ensure no extra spaces in the API key

### Issue: "Queue jobs not processing"

**Solution:**
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Restart queue worker
php artisan queue:restart
php artisan queue:work --tries=3
```

### Issue: "Storage permission denied"

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: "Scan stuck in 'scanning' status"

**Solution:**
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear and restart
php artisan queue:flush
php artisan queue:restart
```

### Issue: "SSL certificate verification failed"

**Solution:**
```bash
# Update CA certificates
# macOS
brew install openssl

# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ca-certificates

# Or disable SSL verification (NOT recommended for production)
# Add to .env:
# CURL_SSL_VERIFY=false
```

## 📊 Performance Optimization

### Enable Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear All Caches
```bash
php artisan optimize:clear
```

### Database Optimization
```bash
# Add indexes for better query performance
php artisan db:seed --class=DatabaseOptimizationSeeder
```

## 🔐 Security Checklist

Before deploying to production:

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Use strong `APP_KEY` (auto-generated)
- [ ] Secure OpenAI API key (use environment variables)
- [ ] Enable HTTPS/SSL
- [ ] Configure proper file permissions (755 for directories, 644 for files)
- [ ] Set up firewall rules
- [ ] Enable rate limiting
- [ ] Configure backup strategy
- [ ] Set up monitoring and logging
- [ ] Review and update security headers

## 📞 Support

If you encounter issues not covered in this guide:

1. Check the main README.md for additional documentation
2. Review the architecture.md for system design details
3. Check Laravel logs: `storage/logs/laravel.log`
4. Enable debug mode temporarily: `APP_DEBUG=true`

## 🎉 Success!

If everything is working:
- ✅ Web server running on http://localhost:8000
- ✅ Queue worker processing scans
- ✅ Database migrations complete
- ✅ OpenAI API connected
- ✅ Security tools accessible

You're ready to start scanning! Visit http://localhost:8000 and submit your first scan.

---

**HACKEX** - Don't launch blind. Scan fast. Launch safe.
