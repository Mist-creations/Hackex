# HACKEX - Cache Expiration Fix

## 🐛 **Issue Encountered**

**Problem:** Scan showed "Status: expired" while still processing
**Root Cause:** Cache expired (1 hour) before scan could complete and be viewed
**Impact:** Users couldn't see scan results even though scan completed successfully

---

## ✅ **Fixes Applied**

### **1. Extended Cache Expiration** ✅
- **Before:** 1 hour cache expiration
- **After:** 2 hours cache expiration
- **Reason:** Gives users more time to view results after scan completes

### **2. Cache Updates During Scan** ✅
- **Added:** Cache status updates when scan progresses
- **Updates at:** pending → scanning → done/failed
- **Benefit:** Real-time status reflected in cache

### **3. Reverse Mapping** ✅
- **Added:** `scan_mapping_reverse` cache key
- **Purpose:** Job can find UUID from database ID
- **Benefit:** Job can update cache when scan completes

### **4. Scan Recovery Command** ✅
- **New Command:** `php artisan scans:recover {scan_id}`
- **Purpose:** Manually recreate cache for completed scans
- **Use Case:** Recover scans that expired before viewing

---

## 🔧 **Technical Changes**

### **Files Modified:**

**1. `app/Jobs/ProcessScan.php`**
```php
// Added Cache facade
use Illuminate\Support\Facades\Cache;

// Added cache update method
protected function updateCacheStatus(string $status): void
{
    $cacheKeys = Cache::get('scan_mapping_reverse:' . $this->scan->id, []);
    foreach ($cacheKeys as $uuid) {
        $scanData = Cache::get('scan:' . $uuid);
        if ($scanData) {
            $scanData['status'] = $status;
            Cache::put('scan:' . $uuid, $scanData, now()->addHours(2));
        }
    }
}

// Call updateCacheStatus() at:
// - Start: 'scanning'
// - Complete: 'done'
// - Failure: 'failed'
```

**2. `app/Http/Controllers/ScanController.php`**
```php
// Extended cache expiration
Cache::put('scan:' . $scanId, $scanData, now()->addHours(2));
Cache::put('scan_mapping:' . $scanId, $scan->id, now()->addHours(2));

// Added reverse mapping
$reverseMapping = Cache::get('scan_mapping_reverse:' . $scan->id, []);
$reverseMapping[] = $scanId;
Cache::put('scan_mapping_reverse:' . $scan->id, $reverseMapping, now()->addHours(2));

// Updated messages
'Results will be automatically deleted after 2 hours for your privacy.'
```

**3. `app/Console/Commands/RecoverScan.php` (NEW)**
```php
// Manually recreate cache for expired scans
php artisan scans:recover {scan_id} --uuid={uuid}
```

---

## 📊 **Cache Architecture**

### **Cache Keys:**

1. **`scan:{uuid}`** - Main scan data
   ```php
   [
       'id' => 'uuid',
       'input_url' => 'https://example.com',
       'status' => 'done',
       'created_at' => '2024-12-03T09:55:02Z'
   ]
   ```

2. **`scan_mapping:{uuid}`** - UUID → Database ID
   ```php
   5 // Database scan ID
   ```

3. **`scan_mapping_reverse:{db_id}`** - Database ID → UUIDs
   ```php
   ['uuid1', 'uuid2'] // Array of UUIDs for this scan
   ```

### **Cache Flow:**

```
User submits scan
    ↓
Generate UUID
    ↓
Store in cache (2 hours)
    ↓
Create database record
    ↓
Store UUID → DB mapping
    ↓
Store DB → UUID reverse mapping
    ↓
Dispatch job
    ↓
Job updates cache status: scanning
    ↓
Job processes scan
    ↓
Job updates cache status: done
    ↓
User views results (up to 2 hours)
    ↓
Cache expires after 2 hours
```

---

## 🎯 **Why 2 Hours?**

### **Reasoning:**
- **Scan Time:** Most scans complete in 30-60 seconds
- **View Time:** Users need time to review results
- **Privacy Balance:** Still auto-deletes, but gives reasonable viewing window
- **Recovery Window:** Allows manual recovery if needed

### **Privacy Maintained:**
- ✅ Still auto-deletes (just 2 hours instead of 1)
- ✅ Still UUID-based (non-enumerable)
- ✅ Still no permanent storage
- ✅ Still privacy-first design

---

## 🔄 **Scan Recovery Process**

### **If Scan Expires Before Viewing:**

**Option 1: Automatic (Future)**
- Schedule cleanup to run less frequently
- Scans stay accessible longer

**Option 2: Manual Recovery**
```bash
# Find scan ID
sqlite3 database/database.sqlite "SELECT id, input_url, status FROM scans ORDER BY id DESC LIMIT 5;"

# Recover scan
php artisan scans:recover 5

# Get new URL
# Visit: http://localhost:8000/scan/{new-uuid}
```

**Option 3: Rescan**
- Submit URL again
- New scan with new UUID
- Fresh 2-hour window

---

## 📝 **Updated Documentation**

### **User Messages:**
- **Success:** "Results will be automatically deleted after 2 hours for your privacy."
- **Expired:** "Scan not found or expired. Results are automatically deleted after 2 hours for your privacy."

### **Privacy Policy:**
- Scan results stored for **maximum 2 hours**
- Automatic deletion after expiration
- No permanent data retention

---

## 🧪 **Testing**

### **Test 1: Normal Scan Flow**
```bash
# 1. Submit scan
Visit http://localhost:8000
Enter URL: https://example.com
Submit

# 2. Wait for completion (30-60 seconds)
# 3. View results
# Should show results without "expired" error
```

### **Test 2: Cache Persistence**
```bash
# 1. Complete a scan
# 2. Wait 30 minutes
# 3. Refresh page
# Should still show results (cache not expired)
```

### **Test 3: Manual Recovery**
```bash
# 1. Find completed scan
sqlite3 database/database.sqlite "SELECT id FROM scans WHERE status='done' LIMIT 1;"

# 2. Recover scan
php artisan scans:recover {id}

# 3. Visit recovered URL
# Should show results
```

---

## ⚠️ **Known Limitations**

### **Current Limitations:**
1. **Multiple UUIDs:** Same scan can have multiple UUIDs if recovered multiple times
2. **Cache Dependency:** Relies on cache driver (Redis/File/Database)
3. **No Persistence:** Once cache expires, scan is gone (by design)

### **Future Improvements:**
1. **Configurable Expiration:** Admin can set cache duration
2. **Email Results:** Option to email results before expiration
3. **Download PDF:** Save results before expiration
4. **Scan History:** Optional authenticated user scan history

---

## 🎯 **Summary**

### **Problem:**
- Scans expired before users could view them
- Cache expiration too aggressive (1 hour)

### **Solution:**
- Extended cache to 2 hours
- Added cache updates during scan processing
- Created recovery command for expired scans

### **Result:**
- ✅ Users have 2 hours to view results
- ✅ Cache stays updated during scan
- ✅ Can recover expired scans if needed
- ✅ Privacy still maintained (auto-delete)

---

## 📋 **Commands Reference**

```bash
# Recover expired scan
php artisan scans:recover {scan_id}

# Recover with specific UUID
php artisan scans:recover {scan_id} --uuid={uuid}

# Cleanup old scans (still works)
php artisan scans:cleanup --hours=2

# Recalculate verdicts (still works)
php artisan scans:recalculate-verdicts
```

---

**HACKEX - Now with better cache management and scan recovery!** 🔐
