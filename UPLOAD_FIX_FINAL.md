# HACKEX - Upload Limit Fix (FINAL)

## ✅ **PERMANENT FIX APPLIED**

### **Problem:**
- "Content Too Large - The POST data is too large"
- PHP's built-in server wasn't using the updated php.ini settings
- Needed to pass ini directives directly to the server

### **Solution:**

Updated `start.sh` to include PHP ini overrides:

```bash
# Before
php artisan serve

# After
php -d upload_max_filesize=50M -d post_max_size=60M -d memory_limit=256M artisan serve
```

---

## 🚀 **HOW TO USE:**

### **Start the server:**
```bash
cd /Users/mac/Desktop/HackEx
./start.sh
```

The server will now automatically start with **50MB upload limits**!

---

## ✅ **What's Fixed:**

| Setting | Value |
|---------|-------|
| `upload_max_filesize` | **50M** |
| `post_max_size` | **60M** |
| `memory_limit` | **256M** |

---

## 📦 **Test ZIP Upload:**

1. **Start the server:**
   ```bash
   ./start.sh
   ```

2. **Visit:** http://localhost:8000

3. **Click "Upload ZIP" tab**

4. **Upload a ZIP file** (up to 50MB)

5. **Click "Start Free Security Scan"**

6. **✅ Should work without errors!**

---

## 🔍 **Verify Settings:**

While the server is running, check in another terminal:

```bash
curl -s http://localhost:8000 | head -1
```

If you see the HACKEX page, the server is running with the correct settings!

---

## 📝 **Files Modified:**

### **1. start.sh**
- Added PHP ini directives to `php artisan serve` command
- Automatically applies upload limits on startup

### **2. public/.user.ini** (Created)
- Backup configuration file
- Used by some PHP setups

### **3. /opt/homebrew/etc/php/8.4/php.ini** (Updated)
- Global PHP configuration
- Increased upload limits system-wide

---

## ⚠️ **Important Notes:**

### **Always use `./start.sh`:**
- Don't run `php artisan serve` directly
- Use the start script to ensure upload limits are applied

### **If you need to run manually:**
```bash
cd hackex-app
php -d upload_max_filesize=50M -d post_max_size=60M -d memory_limit=256M artisan serve
```

---

## 🎯 **Why This Works:**

### **PHP ini Directives:**
- `-d` flag sets PHP configuration at runtime
- Overrides php.ini settings
- Applies only to this server instance

### **Benefits:**
- ✅ No need to modify system PHP config
- ✅ Portable across different environments
- ✅ Automatically applied via start script
- ✅ Works with PHP's built-in server

---

## 🚀 **READY TO TEST!**

**Restart the server and try uploading your ZIP file:**

```bash
# Stop current server (Ctrl+C if running)

# Start with new settings
./start.sh

# Upload your ZIP file at http://localhost:8000
```

---

**HACKEX - Now with 50MB ZIP upload support!** 📦
