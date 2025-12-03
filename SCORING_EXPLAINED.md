# HACKEX - Scoring System Explained

## How Scoring Works

### Base Score: 100 Points

Every scan starts with a perfect score of 100 points. Points are deducted based on the severity of issues found.

---

## Severity Levels & Point Deductions

### Critical (-40 points each)
**Immediate security threats that must be fixed before launch**

Examples:
- Expired SSL certificate
- Missing HTTPS entirely
- Open database ports (MySQL, PostgreSQL, MongoDB, Redis)
- Exposed .env files with database passwords
- Hardcoded AWS credentials in code

### High (-20 points each)
**Serious vulnerabilities that should be fixed soon**

Examples:
- SSL certificate expiring in less than 7 days
- Missing Content-Security-Policy header
- Missing Strict-Transport-Security (HSTS) header
- Publicly accessible admin panels (except WordPress)
- Hardcoded API keys (OpenAI, Stripe, GitHub)

### Medium (-10 points each)
**Security best practices and moderate issues**

Examples:
- SSL certificate expiring in 7-30 days (usually auto-renewed)
- WordPress /wp-admin accessible (normal, but ensure 2FA)
- Missing X-Content-Type-Options header
- Missing X-Frame-Options header
- Debug mode enabled in config files
- Sensitive data in log files

### Low (-3 points each)
**Minor improvements and nice-to-haves**

Examples:
- Missing Referrer-Policy header
- .DS_Store files in uploads
- Minor configuration recommendations

---

## Verdict System

### 80-100 Points: Safe for Launch
**Color:** Green
**Icon:** checkmark
**Meaning:** Your application has good security posture. Minor issues (if any) can be addressed post-launch.

### 50-79 Points: Risky - Fix Recommended
**Color:** Yellow  
**Icon:** Warning
**Meaning:** Your application has vulnerabilities that should be fixed before launch, but they're not critical. Review and address high/medium severity issues.

### 0-49 Points: Critical - Do Not Launch
**Color:** Red
**Icon:** X
**Meaning:** Your application has serious security issues that must be fixed immediately. Do not launch until critical issues are resolved.

---

## Example Calculations

### Example 1: WordPress Site with SSL Expiring Soon
- Base Score: 100
- SSL expiring in 25 days: -10 (medium)
- WordPress /wp-admin accessible: -10 (medium)
- Missing CSP header: -20 (high)
- **Final Score: 60 (Risky - Fix Recommended)**

### Example 2: Well-Secured Modern App
- Base Score: 100
- Missing Referrer-Policy: -3 (low)
- **Final Score: 97 (Safe for Launch)**

### Example 3: Exposed Database
- Base Score: 100
- Open MySQL port: -40 (critical)
- Exposed .env file: -40 (critical)
- Missing HSTS: -20 (high)
- **Final Score: 0 (Critical - Do Not Launch)**

---

## Recent Improvements

### SSL Certificate Expiry
**OLD:** Any SSL expiring in less than 30 days = High (-20 points)  
**NEW:**
- Less than 7 days: High (-20 points)
- 7-30 days: Medium (-10 points) - Auto-renewal usually handles this
- Expired: Critical (-40 points)

**Reasoning:** Most modern hosting providers auto-renew SSL certificates. A cert expiring in 25 days is not urgent.

### WordPress Admin Panel
**OLD:** /wp-admin accessible = Critical (-40 points)  
**NEW:** /wp-admin accessible = Medium (-10 points)

**Reasoning:** WordPress /wp-admin is designed to be public with built-in brute-force protection. It's normal for WordPress sites. Other admin panels remain High severity.

---

## Why Your Score Might Be Lower Than Expected

### Common Scenarios:

1. **WordPress Site Getting Low Score**
   - WordPress sites often have /wp-admin accessible (now only -10)
   - May be missing security headers (-20 each)
   - Solution: Install security plugin to add headers

2. **SSL Expiring Soon**
   - If SSL expires in 7-30 days: -10 points (medium)
   - If SSL expires in less than 7 days: -20 points (high)
   - Solution: Check if auto-renewal is enabled

3. **Missing Security Headers**
   - Each missing header can be -10 to -20 points
   - Common missing headers: CSP, HSTS, X-Frame-Options
   - Solution: Add headers in web server config

4. **Exposed Files**
   - .env files, .git folders, backup files
   - Each can be -40 points (critical)
   - Solution: Configure web server to block access

---

## How to Improve Your Score

### Quick Wins (High Impact):
1. **Enable HTTPS** - Fixes critical issue (+40 points)
2. **Remove exposed .env files** - Fixes critical issue (+40 points)
3. **Close open database ports** - Fixes critical issue (+40 points per port)
4. **Add security headers** - Fixes high issues (+20 points each)

### Medium Impact:
1. **Renew SSL certificate** - If expiring soon (+10-20 points)
2. **Disable debug mode** - In production (+10 points)
3. **Remove sensitive logs** - Clean up log files (+10 points)

### Low Impact:
1. **Add Referrer-Policy header** - Best practice (+3 points)
2. **Remove .DS_Store files** - Cleanup (+3 points)

---

## Understanding Your Results

### Score 80 with "Critical" Verdict = BUG (FIXED)
If you see a score of 80 or higher with a red "Critical" badge, this was a display bug. The logic is:
- 80-100 = Green "Safe for Launch"
- 50-79 = Yellow "Risky"
- 0-49 = Red "Critical"

This should now display correctly.

### Score 0 for WordPress Site
If a WordPress site gets 0 score, it likely has:
- Multiple missing security headers (5 headers × -20 = -100 points)
- This can happen even with no critical vulnerabilities
- Solution: Install a security plugin like Wordfence or Sucuri

---

## Best Practices

### For WordPress Sites:
- Install security plugin (Wordfence, Sucuri, iThemes Security)
- Enable SSL/HTTPS
- Use strong passwords and 2FA
- Keep WordPress and plugins updated
- Add security headers via plugin or .htaccess

### For Custom Applications:
- Always use HTTPS
- Add all security headers
- Never commit .env files to git
- Use environment variables for secrets
- Keep SSL certificates auto-renewing
- Close unnecessary ports

### For All Sites:
- Aim for 80+ score before launch
- Fix all critical issues immediately
- Address high severity issues before launch
- Medium/low issues can be addressed post-launch
- Re-scan after making fixes

---

## Frequently Asked Questions

### Q: Why is my score 80 but showing as "Critical"?
**A:** This was a bug in the display logic. It's now fixed. Score 80 should show as green "Safe for Launch".

### Q: Why is SSL expiring in 25 days marked as an issue?
**A:** It's now marked as Medium (-10 points) since auto-renewal usually handles this. Only SSL expiring in less than 7 days is High severity.

### Q: Why is my WordPress site getting a low score?
**A:** WordPress sites often lack security headers by default. Install a security plugin to add them. Also, /wp-admin being accessible is now only Medium severity (-10 points).

### Q: Can I get a score above 100?
**A:** No, 100 is the maximum. Perfect security = 100 points.

### Q: What's a "good" score for launch?
**A:** 80+ is safe for launch. 90+ is excellent. 100 is perfect (rare).

### Q: Should I fix all issues before launch?
**A:** Fix all Critical and High severity issues. Medium and Low can be addressed post-launch.

---

## Summary

- **Start:** 100 points
- **Deduct:** Based on severity (Critical -40, High -20, Medium -10, Low -3)
- **Verdict:** 80+ = Safe, 50-79 = Risky, 0-49 = Critical
- **Goal:** Aim for 80+ before launch
- **Priority:** Fix Critical and High issues first

**HACKEX helps you understand security in plain English, not just numbers!**
