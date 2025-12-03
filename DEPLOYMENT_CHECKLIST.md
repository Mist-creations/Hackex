# HACKEX - Production Deployment Checklist

## 🚀 Pre-Deployment Checklist

### Environment Configuration
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_URL` to your production domain
- [ ] Generate new `APP_KEY` for production
- [ ] Configure production database (PostgreSQL recommended)
- [ ] Add OpenAI API key with sufficient credits
- [ ] Set appropriate `SCAN_RATE_LIMIT` for production

### Security
- [ ] Review and update `APP_KEY`
- [ ] Secure OpenAI API key (use environment variables, not hardcoded)
- [ ] Enable HTTPS/SSL certificate
- [ ] Configure firewall rules
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Disable directory listing in web server
- [ ] Configure CORS if needed
- [ ] Review and update security headers
- [ ] Enable rate limiting
- [ ] Configure session security settings

### Database
- [ ] Run migrations on production database
- [ ] Verify database connection
- [ ] Set up database backups
- [ ] Configure database connection pooling
- [ ] Optimize database indexes
- [ ] Set up database monitoring

### Queue System
- [ ] Configure Redis for queue (recommended for production)
- [ ] Set up Supervisor for queue workers
- [ ] Configure queue worker restart policies
- [ ] Set up queue monitoring
- [ ] Configure failed job handling
- [ ] Test queue processing

### Web Server
- [ ] Configure Nginx or Apache
- [ ] Set up PHP-FPM
- [ ] Configure proper PHP settings (memory_limit, upload_max_filesize)
- [ ] Enable gzip compression
- [ ] Configure caching headers
- [ ] Set up log rotation
- [ ] Configure error pages (404, 500)

### Performance
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Configure Redis for caching
- [ ] Enable OPcache
- [ ] Optimize Composer autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Configure CDN for static assets (if applicable)

### Monitoring & Logging
- [ ] Set up application monitoring (e.g., Sentry, Bugsnag)
- [ ] Configure log aggregation (e.g., Papertrail, Loggly)
- [ ] Set up uptime monitoring
- [ ] Configure error alerting
- [ ] Set up performance monitoring
- [ ] Configure disk space alerts

### Backup & Recovery
- [ ] Set up automated database backups
- [ ] Test backup restoration process
- [ ] Configure file storage backups
- [ ] Document recovery procedures
- [ ] Set up backup monitoring

### Testing
- [ ] Run all tests: `php artisan test`
- [ ] Test URL scanning functionality
- [ ] Test ZIP upload and scanning
- [ ] Test AI explanation generation
- [ ] Test queue processing
- [ ] Test rate limiting
- [ ] Perform load testing
- [ ] Test error handling

### Documentation
- [ ] Update README with production setup instructions
- [ ] Document deployment process
- [ ] Document rollback procedures
- [ ] Create runbook for common issues
- [ ] Document monitoring and alerting

## 📋 Deployment Steps

### 1. Server Setup

```bash
# Update system packages
sudo apt-get update && sudo apt-get upgrade -y

# Install required packages
sudo apt-get install -y nginx php8.3-fpm php8.3-cli php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-sqlite3 php8.3-pgsql \
    postgresql redis-server supervisor nmap openssl curl unzip

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Application Deployment

```bash
# Clone repository
cd /var/www
git clone <repository-url> hackex
cd hackex

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set up environment
cp .env.example .env
nano .env  # Configure production settings

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/hackex
sudo chmod -R 755 /var/www/hackex
sudo chmod -R 775 /var/www/hackex/storage
sudo chmod -R 775 /var/www/hackex/bootstrap/cache

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Nginx Configuration

Create `/etc/nginx/sites-available/hackex`:

```nginx
server {
    listen 80;
    server_name hackex.app www.hackex.app;
    root /var/www/hackex/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/hackex /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. SSL Certificate (Let's Encrypt)

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d hackex.app -d www.hackex.app
```

### 5. Supervisor Configuration

Create `/etc/supervisor/conf.d/hackex-worker.conf`:

```ini
[program:hackex-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hackex/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hackex/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hackex-worker:*
```

### 6. Cron Jobs

Add to crontab:
```bash
sudo crontab -e -u www-data

# Add this line:
* * * * * cd /var/www/hackex && php artisan schedule:run >> /dev/null 2>&1
```

## 🔍 Post-Deployment Verification

### Functional Tests
- [ ] Homepage loads correctly
- [ ] Can submit URL scan
- [ ] Can upload and scan ZIP file
- [ ] Scan results display correctly
- [ ] AI explanations generate properly
- [ ] Queue workers are processing jobs
- [ ] Rate limiting is working
- [ ] Error pages display correctly

### Performance Tests
- [ ] Page load time < 2 seconds
- [ ] Scan completion time < 60 seconds
- [ ] Queue processing is fast
- [ ] Database queries are optimized
- [ ] Memory usage is acceptable

### Security Tests
- [ ] HTTPS is enforced
- [ ] Security headers are present
- [ ] File uploads are validated
- [ ] Rate limiting prevents abuse
- [ ] Error messages don't leak sensitive info
- [ ] Debug mode is disabled

## 🚨 Rollback Plan

If deployment fails:

```bash
# 1. Revert to previous version
cd /var/www/hackex
git checkout <previous-commit>

# 2. Reinstall dependencies
composer install --optimize-autoloader --no-dev

# 3. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart hackex-worker:*

# 5. Verify application is working
curl -I https://hackex.app
```

## 📊 Monitoring Setup

### Application Monitoring

**Sentry Integration:**
```bash
composer require sentry/sentry-laravel

# Add to .env:
SENTRY_LARAVEL_DSN=your_sentry_dsn
```

**Log Monitoring:**
```bash
# Set up log rotation
sudo nano /etc/logrotate.d/hackex

# Add:
/var/www/hackex/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Uptime Monitoring
- Set up UptimeRobot or Pingdom
- Monitor: https://hackex.app
- Alert on: HTTP errors, slow response times

### Performance Monitoring
- Set up New Relic or Datadog
- Monitor: Response times, database queries, queue processing
- Alert on: Slow queries, high memory usage, queue backlog

## 🔧 Maintenance Tasks

### Daily
- [ ] Check application logs for errors
- [ ] Monitor queue processing
- [ ] Check disk space usage
- [ ] Review failed jobs

### Weekly
- [ ] Review security logs
- [ ] Check backup integrity
- [ ] Review performance metrics
- [ ] Update dependencies (if needed)

### Monthly
- [ ] Security audit
- [ ] Performance optimization review
- [ ] Database optimization
- [ ] Review and update documentation

## 📞 Emergency Contacts

**On-Call Engineer:** [Your contact]  
**Hosting Provider:** [Provider contact]  
**Domain Registrar:** [Registrar contact]  
**SSL Certificate:** [Certificate provider]

## 🎉 Deployment Complete!

Once all checklist items are complete:
- ✅ Application is live
- ✅ Monitoring is active
- ✅ Backups are configured
- ✅ Documentation is updated
- ✅ Team is notified

**HACKEX is now live and ready to help founders launch safely!**

---

**HACKEX** - Don't launch blind. Scan fast. Launch safe.
