# HACKEX - Docker Deployment Guide

## 🐳 Quick Start with Docker

### **Option 1: Docker Compose (Recommended)**

```bash
# Clone the repository
git clone https://github.com/Mist-creations/Hackex.git
cd Hackex

# Build and run
docker-compose up -d

# Access the app
open http://localhost:8080
```

### **Option 2: Docker Build**

```bash
# Build the image
docker build -t hackex:latest .

# Run the container
docker run -d -p 8080:80 \
  -v $(pwd)/hackex-app/storage:/var/www/html/storage \
  -v $(pwd)/hackex-app/database:/var/www/html/database \
  --name hackex \
  hackex:latest

# Access the app
open http://localhost:8080
```

---

## 📋 Requirements

- Docker 20.10+
- Docker Compose 1.29+ (for docker-compose method)

---

## 🔧 Configuration

### **Environment Variables**

Edit `hackex-app/.env` before building:

```env
APP_NAME=HACKEX
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

### **Upload Limits**

The Docker image is configured with:
- **Upload Max Filesize:** 50MB
- **Post Max Size:** 60MB
- **Memory Limit:** 256MB

---

## 🚀 Production Deployment

### **Deploy to Cloud**

#### **DigitalOcean / AWS / GCP**

```bash
# Build for production
docker build -t hackex:production .

# Tag for registry
docker tag hackex:production your-registry/hackex:latest

# Push to registry
docker push your-registry/hackex:latest

# Deploy on server
docker pull your-registry/hackex:latest
docker run -d -p 80:80 \
  -v /var/hackex/storage:/var/www/html/storage \
  -v /var/hackex/database:/var/www/html/database \
  --restart always \
  --name hackex \
  your-registry/hackex:latest
```

#### **Heroku**

```bash
# Login to Heroku
heroku login
heroku container:login

# Create app
heroku create your-hackex-app

# Push container
heroku container:push web -a your-hackex-app
heroku container:release web -a your-hackex-app

# Open app
heroku open -a your-hackex-app
```

---

## 🛠️ Maintenance

### **View Logs**

```bash
docker logs -f hackex
```

### **Restart Container**

```bash
docker restart hackex
```

### **Clear Cache**

```bash
docker exec hackex php artisan cache:clear
docker exec hackex php artisan config:clear
```

### **Run Migrations**

```bash
docker exec hackex php artisan migrate --force
```

### **Access Shell**

```bash
docker exec -it hackex sh
```

---

## 📊 Monitoring

### **Check Queue Worker**

```bash
docker exec hackex ps aux | grep queue
```

### **Check Nginx**

```bash
docker exec hackex nginx -t
```

---

## 🔒 Security Notes

1. **Change APP_KEY** - Generate a new key in production
2. **Disable Debug** - Set `APP_DEBUG=false`
3. **Use HTTPS** - Configure SSL/TLS in production
4. **Limit Upload Size** - Adjust based on your needs
5. **Regular Updates** - Keep Docker image updated

---

## 🎯 Features Included

✅ **Nginx + PHP-FPM** - High performance web server  
✅ **Queue Worker** - Background job processing  
✅ **SQLite Database** - Zero-config database  
✅ **Auto Migrations** - Database setup on start  
✅ **Optimized Autoloader** - Fast class loading  
✅ **Cached Config** - Improved performance  

---

## 🐛 Troubleshooting

### **Container won't start**

```bash
docker logs hackex
```

### **Permission errors**

```bash
docker exec hackex chown -R nginx:nginx /var/www/html/storage
docker exec hackex chmod -R 755 /var/www/html/storage
```

### **Queue not processing**

```bash
docker exec hackex php artisan queue:restart
```

### **Database locked**

```bash
docker exec hackex php artisan cache:clear
docker restart hackex
```

---

## 📝 Notes

- The container runs both Nginx and the queue worker
- Storage and database are mounted as volumes for persistence
- Logs are available via `docker logs`
- The app runs on port 80 inside the container (mapped to 8080 on host)

---

**Ready to deploy! 🚀**
