# HACKEX - PHP Upload Limit Fix

## ✅ **FIXED: Content Too Large Error**

### **Problem:**
- Error: "Content Too Large - The POST data is too large"
- PHP upload limits were too small (2MB)
- Prevented ZIP file uploads

### **Solution Applied:**

Updated PHP configuration file: `/opt/homebrew/etc/php/8.4/php.ini`

**Changes:**
```ini
# Before
upload_max_filesize = 2M
post_max_size = 8M

# After
upload_max_filesize = 50M
post_max_size = 60M
```

---

## 🔄 **RESTART REQUIRED**

### **You need to restart the PHP server:**

**Option 1: If using `php artisan serve`:**
```bash
# Press Ctrl+C to stop the server
# Then restart:
cd /Users/mac/Desktop/HackEx/hackex-app
php artisan serve
```

**Option 2: If using the start script:**
```bash
# Press Ctrl+C to stop
# Then restart:
./start.sh
```

---

## ✅ **After Restart:**

You can now upload ZIP files up to **50MB**!

### **Test it:**
1. Visit: http://localhost:8000
2. Click "Upload ZIP" tab
3. Upload a ZIP file (up to 50MB)
4. Click "Start Free Security Scan"
5. **Should work without errors!**

---

## 📊 **New Upload Limits:**

| Setting | Old Value | New Value |
|---------|-----------|-----------|
| `upload_max_filesize` | 2M | **50M** |
| `post_max_size` | 8M | **60M** |
| `memory_limit` | 128M | 128M (unchanged) |

**Note:** `post_max_size` must be larger than `upload_max_filesize` to account for form data overhead.

---

## 🔍 **Verify Settings:**

After restarting, check if the settings are applied:

```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

**Expected output:**
```
post_max_size => 60M => 60M
upload_max_filesize => 50M => 50M
```

---

## ⚙️ **Additional Configuration:**

### **Laravel Validation:**
The Laravel validation is already set to 50MB in `ScanController.php`:

```php
'zip_file' => 'nullable|file|mimes:zip|max:51200', // 50MB
```

### **Environment Variable:**
The `.env` file also specifies 50MB:

```env
SCAN_MAX_FILE_SIZE=52428800  # 50MB in bytes
```

---

## 🚨 **If Still Getting Errors:**

### **1. Verify PHP Configuration:**
```bash
php -i | grep -E "upload_max_filesize|post_max_size"
```

### **2. Check Web Server:**
If using nginx or Apache, they may have their own limits:

**Nginx:**
```nginx
client_max_body_size 50M;
```

**Apache:**
```apache
LimitRequestBody 52428800
```

### **3. Restart Everything:**
```bash
# Stop queue worker
pkill -f "queue:work"

# Stop web server (Ctrl+C)

# Restart web server
php artisan serve

# Restart queue worker
php artisan queue:work --tries=3 &
```

---

## 📝 **Why This Happened:**

### **Default PHP Limits:**
- PHP ships with conservative defaults (2MB uploads)
- Designed for typical web forms
- Security measure to prevent abuse

### **HACKEX Requirements:**
- Needs to scan source code ZIP files
- Source code can be 10-50MB
- Requires higher limits

---

## ✅ **Summary:**

**Problem:** PHP upload limit too small (2MB)  
**Solution:** Increased to 50MB in php.ini  
**Action Required:** Restart PHP server  
**Result:** Can now upload ZIP files up to 50MB!  

---

**HACKEX - Now with 50MB ZIP upload support!** 📦
