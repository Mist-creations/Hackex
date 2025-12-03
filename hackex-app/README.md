# HACKEX - Pre-Launch Security Scanner

**Tagline:** Scan fast. Launch safe.

HACKEX is a web-based security readiness scanner that helps founders and developers detect real security risks before launching, using live server scans, code hygiene checks, and AI explanations anyone can understand.

## 🎯 What HACKEX Does

- **URL Runtime Scanning:** Checks live websites for SSL issues, missing security headers, exposed admin panels, open ports, and configuration leaks
- **ZIP Source Code Scanning:** Analyzes uploaded project files for hardcoded API keys, .env files, debug flags, private keys, and database dumps
- **AI-Powered Explanations:** Translates technical vulnerabilities into plain English with real-world attack scenarios and clear fix recommendations
- **Security Score & Verdict:** Provides 0-100 score and launch readiness verdict (Safe/Risky/Critical)

## 🚀 Quick Start

### Prerequisites

- PHP 8.3+
- Composer
- SQLite or PostgreSQL
- Server tools: `nmap`, `openssl`, `curl`, `unzip`
- OpenAI API key (for AI explanations)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd hackex-app

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file
# Add your OpenAI API key:
# OPENAI_API_KEY=your_openai_key_here

# Run migrations
php artisan migrate

# Start queue worker (in separate terminal)
php artisan queue:work

# Start development server
php artisan serve
```

Visit `http://localhost:8000` to access HACKEX.

## 🎨 Design Theme

- **Primary Color:** Sky Blue (#0EA5E9)
- **Secondary Color:** Black (#000000)
- **Neutral:** White (#FFFFFF)
- **Style:** Clean, minimal, cybersecurity/SaaS aesthetic

## 📋 Features

### Runtime Security Scan (URL-based)
- ✓ HTTPS/SSL validation and certificate expiration
- ✓ Security headers check (CSP, HSTS, X-Frame-Options, etc.)
- ✓ Exposed admin panels detection
- ✓ Configuration file leaks (.env, .git, backups)
- ✓ Directory listing vulnerabilities
- ✓ Open dangerous ports (MySQL, Redis, SSH, etc.)
- ✓ CORS misconfigurations
- ✓ Debug mode detection

### Static Code Scan (ZIP-based)
- ✓ Hardcoded API keys and secrets (AWS, OpenAI, Stripe, GitHub)
- ✓ Exposed .env files
- ✓ Debug flags enabled
- ✓ Private RSA/SSH keys
- ✓ Database dumps (.sql files)
- ✓ Sensitive information in logs
- ✓ Hardcoded passwords

### AI Explanation Engine
For each vulnerability, AI generates:
1. **Plain English Explanation** - What the issue means
2. **Real-World Attack Scenario** - How hackers exploit it
3. **Business Impact** - Potential damage
4. **Fix Recommendation** - Clear, actionable steps

## 🏗️ Architecture

### Tech Stack
- **Backend:** Laravel 11
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** SQLite (development), PostgreSQL (production)
- **Queue:** Laravel database queue
- **AI:** OpenAI API (GPT-4)

### Key Components

**Models:**
- `Scan` - Scan records with score and verdict
- `Finding` - Individual security issues

**Services:**
- `RuntimeScanner` - Live URL security scanning
- `StaticScanner` - ZIP file code analysis
- `AIExplanationService` - AI-powered explanations

**Jobs:**
- `ProcessScan` - Async scan processing

**Controllers:**
- `HomeController` - Landing page
- `ScanController` - Scan submission and results
- `DashboardController` - Scan history (optional auth)

## 📊 Severity & Scoring

| Severity | Weight | Examples |
|----------|--------|----------|
| Critical | -40 pts | Exposed .env, open database ports, expired SSL |
| High | -20 pts | Missing CSP, public admin panels, hardcoded keys |
| Medium | -10 pts | Missing security headers, sensitive logs |
| Low | -3 pts | Missing referrer policy, .DS_Store files |

**Verdict Logic:**
- 80-100: ✅ Safe for Launch
- 50-79: ⚠️ Risky – Fix Recommended
- 0-49: ❌ Critical – Do Not Launch

## 🔒 Security & Legal

### Rate Limiting
- 5 scans per IP per hour
- 10 scans per day for unauthenticated users

### File Upload Limits
- Max ZIP size: 50MB
- Max extraction size: 100MB

### User Consent
Users must confirm ownership or permission before scanning.

### Data Retention
- Scan results: 30 days
- Uploaded files: Deleted after scan completion
- Logs: 90 days for audit

## 🛠️ Development

### Running Tests
```bash
php artisan test
```

### Queue Worker
```bash
php artisan queue:work --tries=3
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 📝 Environment Variables

```env
# App Configuration
APP_NAME=HACKEX
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hackex.app

# Database
DB_CONNECTION=sqlite

# Queue
QUEUE_CONNECTION=database

# OpenAI Configuration
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4

# Scan Configuration
SCAN_MAX_FILE_SIZE=52428800  # 50MB
SCAN_RATE_LIMIT=5
```

## 🎯 Use Cases

- **Hackathon Teams:** Quick security check before demo
- **Startup Founders:** Pre-launch security validation
- **Freelance Developers:** Client project security audit
- **Indie Developers:** MVP security hygiene check
- **Agencies:** Client deliverable security gate

## 📖 Documentation

- Full architecture: See `/architecture.md` in project root
- API documentation: Coming soon
- Security best practices: Coming soon

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is open-source software licensed under the MIT license.

## 🙏 Acknowledgments

Built with Laravel 11, Tailwind CSS, and OpenAI API.

---

**HACKEX** - Don't launch blind. Scan fast. Launch safe.
