# HACKEX - COMPLETE ARCHITECTURE & TECHNICAL SPECIFICATION

## 1. PRODUCT OVERVIEW

**Product Name:** HACKEX  
**Tagline:** "Scan fast. Launch safe."  
**Purpose:** Pre-launch security scanner for web apps, APIs & MVPs

### What HACKEX Is
HACKEX is a web-based security readiness scanner that allows founders and developers to:
1. Scan a live website or API using its URL
2. Scan their project source code by uploading a ZIP file
3. Instantly detect launch-blocking security weaknesses
4. Get AI-powered plain-English explanations with real-world attack scenarios

### Target Users
- Hackathon teams
- Startup founders
- Freelance developers
- Indie developers
- Agencies launching client projects

### Core Question Answered
**"Is my product safe enough to launch to the public?"**

---

## 2. VISUAL IDENTITY (UI THEME)

**Strict Design System:**
- **Primary Color:** Sky Blue (#0EA5E9)
- **Secondary Color:** Black (#000000)
- **Neutral:** White (#FFFFFF)
- **Style:** Clean, minimal, cybersecurity/SaaS look
- **Typography:** Modern sans-serif (Inter, Poppins, or system fonts)
- **Dashboard Layout:** 
  - Dark header (black)
  - Light content area (white)
  - Sky-blue accents for status, progress, and CTAs

---

## 3. PROBLEMS SOLVED

Most MVPs launch with critical security flaws:
- ✗ Exposed admin panels
- ✗ Debug mode enabled in production
- ✗ Hardcoded API keys and secrets
- ✗ Weak server configuration
- ✗ Missing security headers
- ✗ Open databases and services

**Founder Pain Points:**
- Don't understand technical security risks
- Can't afford security consultants ($5k-$50k)
- Find existing tools too technical
- Need fast, actionable guidance

**HACKEX Solution:**
Turns technical vulnerabilities into human-readable decisions with AI-powered explanations.

---

## 4. CORE FEATURES

### A. URL Runtime Security Scan
Scans live production servers for:
- ✓ HTTPS/SSL validity and certificate expiration
- ✓ Missing security headers (CSP, HSTS, X-Frame-Options, etc.)
- ✓ Public admin pages (/admin, /dashboard, /login)
- ✓ Exposed configuration files (/.env, /.git, /backup.zip)
- ✓ Directory listing vulnerabilities
- ✓ CORS misconfigurations
- ✓ Open dangerous ports (22, 3306, 6379, etc.)
- ✓ Rate-limit weaknesses
- ✓ Framework debug pages

### B. ZIP Source Code Scan
Analyzes uploaded project files for:
- ✓ Hardcoded API keys & secrets (regex patterns)
- ✓ .env files and backups
- ✓ Debug flags (APP_DEBUG=true)
- ✓ Private RSA/SSH keys
- ✓ Database dumps (.sql files)
- ✓ Logs with sensitive information
- ✓ Poor dependency hygiene
- ✓ Leaked tokens in configuration files

### C. AI Security Explanation Engine
**AI Role:** Translation & Education (NOT detection)

For each vulnerability found, AI generates:
1. **Plain English Explanation** - What the issue means
2. **Real-World Attack Scenario** - How hackers exploit it
3. **Business Impact** - Potential damage to the company
4. **Fix Recommendation** - Clear, actionable steps

**Example AI Output:**
```
Issue: Exposed Environment File
Severity: Critical

What This Means:
Your application's secret configuration file is publicly visible on the internet.

Real-World Attack Scenario:
A hacker can download your database password, log in directly to your server, 
and steal or delete all customer data within minutes.

Business Impact:
This can lead to total data loss, legal liability, customer trust collapse, 
and permanent shutdown.

How To Fix:
Move the .env file outside the public directory and block access via your 
web server configuration (.htaccess or nginx.conf).
```

### D. Security Score & Verdict System
Each scan returns:
- **Score:** 0-100 (calculated from severity weights)
- **Verdict:**
  - ✅ **Safe for Launch** (80-100)
  - ⚠️ **Risky – Fix Recommended** (50-79)
  - ❌ **Critical – Do Not Launch** (0-49)

---

## 5. SECURITY DETECTION RULES

### Runtime Scan (URL-based)
| Issue | Severity | Detection Method |
|-------|----------|------------------|
| Invalid/Missing HTTPS | Critical | SSL certificate check |
| Expired TLS certificate | Critical | OpenSSL verification |
| Missing CSP header | High | HTTP header analysis |
| Missing HSTS header | High | HTTP header analysis |
| Public admin routes | Critical | Path enumeration |
| Exposed .env file | Critical | HTTP request test |
| Exposed .git directory | Critical | HTTP request test |
| Directory listing enabled | High | Directory index check |
| Open MySQL port (3306) | Critical | nmap port scan |
| Open Redis port (6379) | Critical | nmap port scan |
| Open SSH port (22) | Medium | nmap port scan |
| Wildcard CORS (*) | High | CORS header check |
| Debug error pages visible | High | Error page detection |
| No rate limiting | Medium | Endpoint stress test |

### Static Scan (ZIP-based)
| Issue | Severity | Detection Method |
|-------|----------|------------------|
| Hardcoded API keys | Critical | Regex pattern matching |
| AWS credentials | Critical | Regex pattern matching |
| Private RSA keys (.pem) | Critical | File extension scan |
| .env files | Critical | Filename search |
| Debug flags enabled | High | Config file parsing |
| SQL database dumps | High | File extension scan |
| Log files with secrets | Medium | Content analysis |
| Hardcoded passwords | High | Regex pattern matching |
| Old framework versions | Low | Dependency analysis |

---

## 6. SEVERITY & SCORING SYSTEM

### Severity Weights
| Severity | Weight Deduction |
|----------|------------------|
| Critical | -40 points |
| High | -20 points |
| Medium | -10 points |
| Low | -3 points |

### Score Calculation
```
Base Score = 100
Final Score = 100 - (sum of all severity deductions)
```

### Verdict Logic
```php
if ($score >= 80) {
    return 'Safe for Launch';
} elseif ($score >= 50) {
    return 'Risky – Fix Recommended';
} else {
    return 'Critical – Do Not Launch';
}
```

---

## 7. TECHNOLOGY STACK

### Backend
- **Framework:** Laravel 11
- **PHP Version:** 8.3+
- **Queue System:** Laravel database queue
- **Storage:** Local filesystem
- **Database:** SQLite (hackathon), PostgreSQL (production)

### Server Tools (Required)
- `nmap` - Port scanning
- `openssl` - SSL certificate validation
- `curl` - HTTP requests
- `unzip` - ZIP file extraction

### Frontend
- **Template Engine:** Blade
- **CSS Framework:** Tailwind CSS
- **Theme:** Sky Blue (#0EA5E9) / Black / White
- **JavaScript:** Alpine.js (optional for interactivity)

### AI Integration
- **Provider:** OpenAI-compatible API (OpenAI, Anthropic, etc.)
- **Purpose:** Explanation generation ONLY (not vulnerability detection)
- **Model:** GPT-4 or Claude 3.5 Sonnet

---

## 8. LARAVEL ROUTING (web.php ONLY)

**All functionality is web-based. No API routes.**

```php
<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScanHistoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', HomeController::class)->name('home');

// Scan routes
Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
Route::get('/scan/{scan}', [ScanController::class, 'show'])->name('scan.show');
Route::get('/scan/{scan}/status', [ScanController::class, 'status'])->name('scan.status');

// Authenticated routes (optional for MVP)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/scan-history', ScanHistoryController::class)->name('scan.history');
});
```

---

## 9. DATABASE SCHEMA

### Scans Table
```php
Schema::create('scans', function (Blueprint $table) {
    $table->id();
    $table->string('input_url')->nullable();
    $table->string('uploaded_zip_path')->nullable();
    $table->integer('score')->default(0);
    $table->string('verdict')->nullable();
    $table->enum('status', ['pending', 'scanning', 'done', 'failed'])->default('pending');
    $table->timestamps();
});
```

### Findings Table
```php
Schema::create('findings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scan_id')->constrained()->onDelete('cascade');
    $table->enum('type', ['runtime', 'static']);
    $table->string('title');
    $table->enum('severity', ['critical', 'high', 'medium', 'low']);
    $table->string('location')->nullable();
    $table->text('evidence')->nullable();
    $table->text('ai_explanation')->nullable();
    $table->text('ai_attack_scenario')->nullable();
    $table->text('fix_recommendation')->nullable();
    $table->timestamps();
});
```

---

## 10. BACKEND SCAN FLOW

```
1. User submits URL and/or ZIP file
   ↓
2. Laravel stores scan record (status: pending)
   ↓
3. Laravel dispatches queue job (ProcessScan)
   ↓
4. Runtime scanner runs (if URL provided)
   - SSL check
   - Header analysis
   - Port scanning
   - Path enumeration
   ↓
5. Static scanner runs (if ZIP provided)
   - Extract ZIP
   - Regex pattern matching
   - File analysis
   ↓
6. Findings normalized & stored in database
   ↓
7. Score computed from severity weights
   ↓
8. AI generates explanations for each finding
   - Plain explanation
   - Attack scenario
   - Fix recommendation
   ↓
9. Final verdict calculated and saved
   ↓
10. User views results on web dashboard
```

---

## 11. AI PROMPT STRUCTURE

### System Prompt
```
You are a cybersecurity assistant that explains vulnerabilities to non-technical 
founders in clear, human language. Always include:

1. What this issue means in simple terms
2. What attackers can do in the real world
3. The real business impact (data loss, legal issues, etc.)
4. A simple, actionable fix

Be direct, avoid jargon, and focus on business consequences.
```

### User Prompt Template
```
Issue: {finding_title}
Severity: {severity}
Evidence: {evidence}
Location: {location}

Generate:
1. Plain explanation (2-3 sentences)
2. Real-world attack scenario (specific example)
3. Business impact (consequences)
4. Fix recommendation (clear steps)
```

---

## 12. FRONTEND PAGES

### 1. Landing Page (`/`)
- Hero section with scan form
- "Scan Your Website" input (URL + ZIP upload)
- Feature list (3 columns)
- Security disclaimer
- CTA: "Start Free Scan"

### 2. Scan Progress Page (`/scan/{id}`)
- Animated scanning status
- Sky-blue progress bar
- Live status messages:
  - "Checking SSL certificate..."
  - "Scanning for exposed secrets..."
  - "Analyzing security headers..."

### 3. Results Page (`/scan/{id}`)
- **Score Card:** Large score display with verdict badge
- **Issue Breakdown:** Grouped by severity
- **AI Explanation Panels:** Expandable cards for each finding
- **Download Report:** PDF export (future feature)

### 4. Dashboard (`/dashboard`) - Optional
- Scan history table
- Risk trend chart
- Re-scan button

---

## 13. LEGAL & USAGE PROTECTION

### User Confirmation Required
Before scanning, user must check:
```
☑ I own this website or have explicit permission to scan it
```

### Rate Limiting
- 5 scans per IP per hour
- 10 scans per day for unauthenticated users

### File Upload Limits
- Max ZIP size: 50MB
- Max extraction size: 100MB

### Data Retention
- Scan results: 30 days
- Uploaded files: Deleted after scan completion
- Logs: Retained for audit (90 days)

---

## 14. HACKATHON MVP SCOPE

### ✅ IN SCOPE
- URL runtime scanning
- ZIP static scanning
- AI-powered explanations
- Security score + verdict
- Web dashboard (Blade + Tailwind)
- Sky blue/black/white UI theme
- Laravel-only architecture
- SQLite database

### ❌ OUT OF SCOPE
- Mobile app (no Flutter)
- GitHub integration
- CI/CD automation
- Continuous monitoring
- User authentication (optional)
- Payment system
- API endpoints for external tools

---

## 15. DEPLOYMENT REQUIREMENTS

### Server Requirements
- PHP 8.3+
- Composer
- SQLite or PostgreSQL
- nmap, openssl, curl, unzip

### Environment Variables
```env
APP_NAME=HACKEX
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hackex.app

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database

OPENAI_API_KEY=your_openai_key
AI_MODEL=gpt-4

SCAN_MAX_FILE_SIZE=52428800
SCAN_RATE_LIMIT=5
```

### Queue Worker
```bash
php artisan queue:work --tries=3
```

---

## 16. SUCCESS METRICS

### Technical Metrics
- Scan completion time < 60 seconds
- AI explanation generation < 10 seconds
- 95% scan success rate

### User Metrics
- Clear verdict understanding (user testing)
- Actionable fix recommendations
- Reduced launch anxiety

---

## 17. ONE-SENTENCE PITCH

**HACKEX helps founders detect real security risks before launching, using live server scans, code hygiene checks, and AI explanations anyone can understand.**

---

## 18. TAGLINE OPTIONS

1. "Don't launch blind."
2. "Scan fast. Launch safe."
3. "Security clarity before exposure."

---

## 19. FUTURE ENHANCEMENTS (POST-MVP)

- GitHub repository integration
- Scheduled re-scans
- Slack/Discord notifications
- Team collaboration features
- Compliance reports (GDPR, SOC2)
- API for CI/CD integration
- Mobile app (if needed)

---

## 20. REVISION HISTORY

| Date | Version | Changes |
|------|---------|---------|
| 2024-12-02 | 1.0 | Initial architecture document |

---

**END OF ARCHITECTURE DOCUMENT**
