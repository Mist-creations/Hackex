# HACKEX - Complete File Structure

## 📁 Project Directory Overview

```
/Users/mac/Desktop/HackEx/
├── architecture.md              # Complete technical specification
├── SETUP.md                     # Detailed installation guide
├── QUICK_START.md              # 5-minute quick start
├── PROJECT_SUMMARY.md          # Implementation summary
├── FILE_STRUCTURE.md           # This file
└── hackex-app/                 # Laravel application
    ├── app/
    │   ├── Http/
    │   │   └── Controllers/
    │   │       ├── HomeController.php           # Landing page
    │   │       ├── ScanController.php           # Scan submission & results
    │   │       ├── DashboardController.php      # Dashboard (optional auth)
    │   │       └── ScanHistoryController.php    # Scan history
    │   ├── Jobs/
    │   │   └── ProcessScan.php                  # Async scan processing
    │   ├── Models/
    │   │   ├── Scan.php                         # Scan model with scoring logic
    │   │   ├── Finding.php                      # Finding model
    │   │   └── User.php                         # User model (Laravel default)
    │   └── Services/
    │       ├── RuntimeScanner.php               # Live URL security scanning
    │       ├── StaticScanner.php                # ZIP file code analysis
    │       └── AIExplanationService.php         # AI-powered explanations
    ├── bootstrap/
    │   └── app.php                              # Application bootstrap
    ├── config/
    │   ├── app.php                              # App configuration
    │   ├── database.php                         # Database configuration
    │   ├── queue.php                            # Queue configuration
    │   └── services.php                         # Third-party services (OpenAI)
    ├── database/
    │   ├── migrations/
    │   │   ├── 0001_01_01_000000_create_users_table.php
    │   │   ├── 0001_01_01_000001_create_cache_table.php
    │   │   ├── 0001_01_01_000002_create_jobs_table.php
    │   │   ├── 2024_12_02_000001_create_scans_table.php
    │   │   └── 2024_12_02_000002_create_findings_table.php
    │   └── database.sqlite                      # SQLite database file
    ├── public/
    │   └── index.php                            # Application entry point
    ├── resources/
    │   └── views/
    │       ├── layouts/
    │       │   └── app.blade.php                # Master layout (sky blue theme)
    │       ├── scan/
    │       │   └── show.blade.php               # Scan results page
    │       └── home.blade.php                   # Landing page
    ├── routes/
    │   └── web.php                              # Web routes (all routes here)
    ├── storage/
    │   ├── app/
    │   │   ├── scans/                           # Temporary scan extractions
    │   │   └── uploads/                         # Uploaded ZIP files
    │   ├── framework/
    │   │   ├── cache/
    │   │   ├── sessions/
    │   │   └── views/
    │   └── logs/
    │       └── laravel.log                      # Application logs
    ├── .env                                     # Environment configuration (create from .env.example)
    ├── .env.example                             # Environment template
    ├── artisan                                  # Laravel CLI
    ├── composer.json                            # PHP dependencies
    ├── composer.lock                            # Locked dependency versions
    └── README.md                                # Project documentation
```

## 🎯 Key Files Explained

### Core Application Files

#### **app/Http/Controllers/**
Contains all web controllers for handling HTTP requests.

**HomeController.php**
- Displays the landing page
- Single action controller (`__invoke`)

**ScanController.php**
- `store()` - Handles scan submission (URL or ZIP)
- `show()` - Displays scan results
- `status()` - AJAX endpoint for scan progress polling

**DashboardController.php**
- Displays user dashboard with scan statistics
- Requires authentication (optional for MVP)

**ScanHistoryController.php**
- Shows paginated list of past scans
- Requires authentication (optional for MVP)

#### **app/Jobs/**

**ProcessScan.php**
- Async job for processing scans
- Orchestrates runtime and static scanners
- Generates AI explanations
- Calculates scores and verdicts
- Handles errors and retries

#### **app/Models/**

**Scan.php**
- Represents a security scan
- Methods:
  - `calculateScore()` - Computes 0-100 score
  - `determineVerdict()` - Returns Safe/Risky/Critical
  - `findingsBySeverity()` - Groups findings by severity
  - `isComplete()` - Check if scan is done
  - `isScanning()` - Check if scan is in progress

**Finding.php**
- Represents a single security issue
- Attributes:
  - `type` - runtime or static
  - `title` - Issue name
  - `severity` - critical, high, medium, low
  - `location` - Where the issue was found
  - `evidence` - Proof of the issue
  - `ai_explanation` - Plain English explanation
  - `ai_attack_scenario` - Real-world attack example
  - `fix_recommendation` - How to fix it

#### **app/Services/**

**RuntimeScanner.php**
- Scans live URLs for security issues
- Methods:
  - `scan($url)` - Main scan method
  - `checkHttps()` - HTTPS validation
  - `checkSslCertificate()` - SSL cert expiration
  - `checkSecurityHeaders()` - Missing headers
  - `checkExposedFiles()` - .env, .git, backups
  - `checkAdminRoutes()` - Public admin panels
  - `checkDirectoryListing()` - Directory traversal
  - `checkOpenPorts()` - Dangerous open ports
  - `checkCors()` - CORS misconfigurations

**StaticScanner.php**
- Analyzes ZIP files for code vulnerabilities
- Methods:
  - `scan($zipPath)` - Main scan method
  - `extractZip()` - Safe ZIP extraction
  - `checkForSecrets()` - Hardcoded API keys
  - `checkForEnvFiles()` - Exposed .env files
  - `checkForDebugFlags()` - Debug mode enabled
  - `checkForPrivateKeys()` - RSA/SSH keys
  - `checkForDatabaseDumps()` - SQL files
  - `checkForSensitiveLogs()` - Logs with secrets
  - `checkForHardcodedPasswords()` - Password strings

**AIExplanationService.php**
- Generates AI-powered explanations
- Methods:
  - `generateExplanation($finding)` - Single finding
  - `generateBatchExplanations($findings)` - Multiple findings
  - `buildPrompt()` - Creates AI prompt
  - `callAI()` - Calls OpenAI API
  - `parseResponse()` - Parses AI response
  - `getFallbackExplanation()` - Fallback when AI fails

### Frontend Files

#### **resources/views/layouts/app.blade.php**
Master layout with:
- Black header with HACKEX logo
- Navigation menu
- Main content area
- Footer
- Tailwind CSS (CDN)
- Alpine.js (CDN)

#### **resources/views/home.blade.php**
Landing page with:
- Hero section with tagline
- Scan form (URL or ZIP)
- Tab switcher (Alpine.js)
- Consent checkbox
- Features section (3 columns)
- How It Works section
- CTA section

#### **resources/views/scan/show.blade.php**
Results page with:
- Scanning progress (if in progress)
- Security score card
- Verdict badge (Safe/Risky/Critical)
- Findings grouped by severity
- Expandable finding cards
- AI explanations (collapsible)
- AJAX polling for status updates

### Configuration Files

#### **.env.example**
Template for environment variables:
- App configuration
- Database settings
- Queue configuration
- OpenAI API key
- Scan limits

#### **config/services.php**
Third-party service configuration:
- OpenAI API settings
- API URL
- Model selection (GPT-4)

### Database Files

#### **database/migrations/**
Database schema definitions:
- `create_scans_table` - Scan records
- `create_findings_table` - Security findings
- `create_jobs_table` - Queue jobs
- `create_cache_table` - Cache storage

#### **database/database.sqlite**
SQLite database file (created after migration)

### Routes

#### **routes/web.php**
All application routes:
```php
GET  /                    - Landing page
POST /scan                - Submit scan
GET  /scan/{id}          - View results
GET  /scan/{id}/status   - Status polling
GET  /dashboard          - Dashboard (auth)
GET  /scan-history       - History (auth)
```

## 📝 File Naming Conventions

### Controllers:
- PascalCase with "Controller" suffix
- Example: `ScanController.php`

### Models:
- PascalCase, singular
- Example: `Scan.php`, `Finding.php`

### Services:
- PascalCase with "Service" suffix (if applicable)
- Example: `RuntimeScanner.php`, `AIExplanationService.php`

### Jobs:
- PascalCase, descriptive action
- Example: `ProcessScan.php`

### Views:
- snake_case with `.blade.php` extension
- Example: `home.blade.php`, `show.blade.php`

### Migrations:
- Timestamp prefix + descriptive name
- Example: `2024_12_02_000001_create_scans_table.php`

## 🔍 Finding Files Quickly

### By Feature:

**URL Scanning:**
- `app/Services/RuntimeScanner.php`
- `app/Jobs/ProcessScan.php` (orchestration)

**ZIP Scanning:**
- `app/Services/StaticScanner.php`
- `app/Jobs/ProcessScan.php` (orchestration)

**AI Explanations:**
- `app/Services/AIExplanationService.php`
- `config/services.php` (OpenAI config)

**Scoring System:**
- `app/Models/Scan.php` (calculateScore, determineVerdict)

**Frontend:**
- `resources/views/home.blade.php` (landing)
- `resources/views/scan/show.blade.php` (results)
- `resources/views/layouts/app.blade.php` (layout)

**Database:**
- `database/migrations/` (schema)
- `app/Models/` (Eloquent models)

**Routes:**
- `routes/web.php` (all routes)

**Configuration:**
- `.env` (environment variables)
- `config/services.php` (third-party services)

## 🎨 Asset Locations

### CSS:
- Tailwind CSS loaded via CDN in `layouts/app.blade.php`
- Custom styles inline in Blade templates

### JavaScript:
- Alpine.js loaded via CDN in `layouts/app.blade.php`
- Custom JS inline in Blade templates (scan polling)

### Images:
- SVG icons inline in Blade templates
- No external image files (all SVG)

## 📊 Log Files

### Application Logs:
- Location: `storage/logs/laravel.log`
- Contains: Errors, warnings, info messages

### Queue Logs:
- Location: Console output when running `queue:work`
- Can redirect to: `storage/logs/worker.log`

### Web Server Logs:
- Location: Depends on server (Nginx/Apache)
- Laravel built-in server: Console output

## 🗄️ Storage Directories

### Uploaded Files:
- Location: `storage/app/uploads/`
- Contains: User-uploaded ZIP files
- Cleanup: Deleted after scan completion

### Extracted Files:
- Location: `storage/app/scans/`
- Contains: Temporary extracted ZIP contents
- Cleanup: Deleted after scan completion

### Cache:
- Location: `storage/framework/cache/`
- Contains: Application cache data

### Sessions:
- Location: `storage/framework/sessions/`
- Contains: User session data

## 🔧 Important Commands

### File Permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

### Clear Caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### View Routes:
```bash
php artisan route:list
```

### View Database Schema:
```bash
php artisan migrate:status
```

## 📦 Dependencies

### PHP Packages (composer.json):
- laravel/framework: ^11.0
- guzzlehttp/guzzle: ^7.2
- laravel/sanctum: ^4.0
- laravel/tinker: ^2.9

### Frontend Libraries (CDN):
- Tailwind CSS: Latest
- Alpine.js: 3.x

## 🎯 Quick Navigation

**Want to modify...**

**Scan logic?**
→ `app/Services/RuntimeScanner.php` or `StaticScanner.php`

**AI prompts?**
→ `app/Services/AIExplanationService.php`

**Scoring system?**
→ `app/Models/Scan.php` (calculateScore method)

**UI design?**
→ `resources/views/` (Blade templates)

**Routes?**
→ `routes/web.php`

**Database schema?**
→ `database/migrations/`

**Configuration?**
→ `.env` or `config/`

---

**HACKEX** - Complete file structure reference
