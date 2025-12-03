<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RuntimeScanner
{
    protected array $findings = [];

    /**
     * Scan a live URL for security vulnerabilities.
     */
    public function scan(string $url): array
    {
        $this->findings = [];

        // Normalize URL
        $url = $this->normalizeUrl($url);

        // Run all runtime checks
        $this->checkHttps($url);
        $this->checkSslCertificate($url);
        $this->checkSecurityHeaders($url);
        $this->checkExposedFiles($url);
        $this->checkAdminRoutes($url);
        $this->checkDirectoryListing($url);
        $this->checkOpenPorts($url);
        $this->checkCors($url);

        return $this->findings;
    }

    /**
     * Normalize URL to ensure proper format.
     */
    protected function normalizeUrl(string $url): string
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }

    /**
     * Check if HTTPS is enabled.
     */
    protected function checkHttps(string $url): void
    {
        if (!str_starts_with($url, 'https://')) {
            $this->addFinding([
                'type' => 'runtime',
                'title' => 'Missing HTTPS/SSL',
                'severity' => 'critical',
                'location' => $url,
                'evidence' => 'Website is not using HTTPS encryption',
            ]);
        }
    }

    /**
     * Check SSL certificate validity.
     */
    protected function checkSslCertificate(string $url): void
    {
        try {
            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT) ?? 443;

            $output = shell_exec("echo | openssl s_client -servername {$host} -connect {$host}:{$port} 2>/dev/null | openssl x509 -noout -dates 2>/dev/null");

            if ($output && str_contains($output, 'notAfter')) {
                preg_match('/notAfter=(.+)/', $output, $matches);
                if (isset($matches[1])) {
                    $expiryDate = strtotime($matches[1]);
                    $daysUntilExpiry = ($expiryDate - time()) / 86400;

                    if ($daysUntilExpiry < 0) {
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'Expired SSL Certificate',
                            'severity' => 'critical',
                            'location' => $host,
                            'evidence' => "SSL certificate expired on {$matches[1]}",
                        ]);
                    } elseif ($daysUntilExpiry < 7) {
                        // Less than 7 days - High severity
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'SSL Certificate Expiring Very Soon',
                            'severity' => 'high',
                            'location' => $host,
                            'evidence' => "SSL certificate expires in " . round($daysUntilExpiry) . " days",
                        ]);
                    } elseif ($daysUntilExpiry < 30) {
                        // 7-30 days - Medium severity (auto-renewal usually handles this)
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'SSL Certificate Expiring Soon',
                            'severity' => 'medium',
                            'location' => $host,
                            'evidence' => "SSL certificate expires in " . round($daysUntilExpiry) . " days (usually auto-renewed)",
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("SSL check failed for {$url}: " . $e->getMessage());
        }
    }

    /**
     * Check for missing security headers.
     */
    protected function checkSecurityHeaders(string $url): void
    {
        try {
            // Follow redirects to get final destination headers
            $response = Http::timeout(10)->withOptions(['allow_redirects' => true])->get($url);
            $headers = $response->headers();

            $requiredHeaders = [
                'Content-Security-Policy' => 'medium',  // Reduced from critical - many major sites don't use CSP
                'Strict-Transport-Security' => 'high',
                'X-Frame-Options' => 'medium',  // Reduced from high
                'X-Content-Type-Options' => 'low',  // Reduced from medium
                'X-XSS-Protection' => 'low',  // Reduced from medium - deprecated header
                'Referrer-Policy' => 'low',
            ];

            // Check for modern alternative security headers (COOP, COEP, etc.)
            // Note: HTTP headers are case-insensitive, check both cases
            $hasModernSecurity = isset($headers['Cross-Origin-Opener-Policy']) ||
                                isset($headers['cross-origin-opener-policy']) ||
                                isset($headers['Cross-Origin-Embedder-Policy']) ||
                                isset($headers['cross-origin-embedder-policy']) ||
                                isset($headers['Cross-Origin-Resource-Policy']) ||
                                isset($headers['cross-origin-resource-policy']) ||
                                isset($headers['Origin-Agent-Cluster']) ||
                                isset($headers['origin-agent-cluster']);

            foreach ($requiredHeaders as $header => $severity) {
                if (!isset($headers[$header])) {
                    // If CSP is missing but modern headers are present, reduce severity
                    if ($header === 'Content-Security-Policy' && $hasModernSecurity) {
                        $severity = 'low'; // Reduce from medium - they use modern alternatives
                    }
                    
                    $this->addFinding([
                        'type' => 'runtime',
                        'title' => "Missing {$header} Header",
                        'severity' => $severity,
                        'location' => $url,
                        'evidence' => "Security header '{$header}' is not set",
                    ]);
                }
            }
            
            // Award positive findings for modern security headers (case-insensitive)
            if (isset($headers['Cross-Origin-Opener-Policy']) || isset($headers['cross-origin-opener-policy'])) {
                $value = $headers['Cross-Origin-Opener-Policy'][0] ?? $headers['cross-origin-opener-policy'][0] ?? 'enabled';
                $this->addFinding([
                    'type' => 'runtime',
                    'title' => 'Modern Cross-Origin Isolation (COOP)',
                    'severity' => 'positive',
                    'location' => $url,
                    'evidence' => 'Cross-Origin-Opener-Policy header is set: ' . $value,
                ]);
            }
            
            if (isset($headers['Cross-Origin-Embedder-Policy']) || isset($headers['cross-origin-embedder-policy']) ||
                isset($headers['Cross-Origin-Embedder-Policy-Report-Only']) || isset($headers['cross-origin-embedder-policy-report-only'])) {
                $this->addFinding([
                    'type' => 'runtime',
                    'title' => 'Modern Resource Isolation (COEP)',
                    'severity' => 'positive',
                    'location' => $url,
                    'evidence' => 'Cross-Origin-Embedder-Policy is configured',
                ]);
            }
            
            if (isset($headers['Reporting-Endpoints']) || isset($headers['reporting-endpoints']) ||
                isset($headers['Report-To']) || isset($headers['report-to'])) {
                $this->addFinding([
                    'type' => 'runtime',
                    'title' => 'Security Monitoring Enabled',
                    'severity' => 'positive',
                    'location' => $url,
                    'evidence' => 'Reporting API configured for security monitoring',
                ]);
            }

            // Check for debug mode indicators
            $body = $response->body();
            if (str_contains($body, 'APP_DEBUG') || str_contains($body, 'Whoops')) {
                $this->addFinding([
                    'type' => 'runtime',
                    'title' => 'Debug Mode Enabled',
                    'severity' => 'high',
                    'location' => $url,
                    'evidence' => 'Debug error pages are publicly visible',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Header check failed for {$url}: " . $e->getMessage());
        }
    }

    /**
     * Check for exposed sensitive files.
     */
    protected function checkExposedFiles(string $url): void
    {
        $sensitiveFiles = [
            '/.env' => 'critical',
            '/.env.backup' => 'critical',
            '/.git/config' => 'critical',
            '/.git/HEAD' => 'critical',
            '/backup.zip' => 'high',
            '/backup.sql' => 'high',
            '/db.sql' => 'high',
            '/phpinfo.php' => 'high',
            '/config.php.bak' => 'medium',
            '/.DS_Store' => 'low',
        ];

        foreach ($sensitiveFiles as $file => $severity) {
            try {
                $response = Http::timeout(5)->get($url . $file);
                
                if ($response->successful() && strlen($response->body()) > 0) {
                    $body = $response->body();
                    
                    // Check if it's actually the sensitive file, not an error page
                    $isActualFile = false;
                    
                    // For .env files, check for environment variable patterns
                    if (str_contains($file, '.env')) {
                        $isActualFile = preg_match('/^[A-Z_]+=.+$/m', $body) || 
                                       str_contains($body, 'APP_KEY=') ||
                                       str_contains($body, 'DB_PASSWORD=');
                    }
                    // For .git files, check for git-specific content
                    elseif (str_contains($file, '.git')) {
                        $isActualFile = str_contains($body, '[core]') || 
                                       str_contains($body, 'repositoryformatversion') ||
                                       str_contains($body, 'ref:');
                    }
                    // For SQL/backup files, check for SQL patterns
                    elseif (str_contains($file, '.sql') || str_contains($file, '.zip')) {
                        $isActualFile = str_contains($body, 'CREATE TABLE') ||
                                       str_contains($body, 'INSERT INTO') ||
                                       $response->header('Content-Type') === 'application/zip';
                    }
                    // For PHP files, check for PHP code
                    elseif (str_contains($file, '.php')) {
                        $isActualFile = str_contains($body, '<?php') ||
                                       str_contains($body, 'phpinfo()');
                    }
                    // For other files, check if it's not an HTML error page
                    else {
                        $isActualFile = !str_contains($body, '<html') && 
                                       !str_contains($body, '<!DOCTYPE');
                    }
                    
                    if ($isActualFile) {
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'Exposed Sensitive File',
                            'severity' => $severity,
                            'location' => $url . $file,
                            'evidence' => "File '{$file}' is publicly accessible",
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // File not accessible - this is good
            }
        }
    }

    /**
     * Check for exposed admin routes with intelligent detection.
     */
    protected function checkAdminRoutes(string $url): void
    {
        $adminPaths = [
            '/admin',
            '/administrator',
            '/wp-admin',
            '/dashboard',
            '/panel',
            '/control',
        ];

        foreach ($adminPaths as $path) {
            try {
                $response = Http::timeout(5)->withOptions(['allow_redirects' => false])->get($url . $path);
                
                // Skip if redirects (likely protected or doesn't exist)
                if ($response->redirect()) {
                    continue;
                }
                
                if ($response->successful()) {
                    $body = $response->body();
                    $bodyLower = strtolower($body);
                    
                    // Smart detection: Check for actual admin panel indicators
                    $hasLoginForm = preg_match('/<form[^>]*(?:action|method)[^>]*>/i', $body) &&
                                   (str_contains($bodyLower, 'type="password"') || 
                                    str_contains($bodyLower, 'name="password"'));
                    
                    $hasAdminKeywords = (str_contains($bodyLower, 'admin login') || 
                                        str_contains($bodyLower, 'administrator login') ||
                                        str_contains($bodyLower, 'dashboard login') ||
                                        str_contains($bodyLower, 'control panel'));
                    
                    $hasAuthHeaders = $response->header('WWW-Authenticate') !== null;
                    
                    // Exclude false positives: social media profiles, public pages
                    $isFalsePositive = str_contains($bodyLower, 'facebook.com') ||
                                      str_contains($bodyLower, 'twitter.com') ||
                                      str_contains($bodyLower, 'instagram.com') ||
                                      str_contains($bodyLower, 'linkedin.com') ||
                                      str_contains($bodyLower, 'user profile') ||
                                      str_contains($bodyLower, 'public profile') ||
                                      preg_match('/@\w+/', $body); // Social media handles
                    
                    // Only flag if it's actually an admin panel
                    if (($hasLoginForm || $hasAdminKeywords || $hasAuthHeaders) && !$isFalsePositive) {
                        // Test for rate limiting by making multiple requests
                        $hasRateLimiting = $this->testRateLimiting($url . $path);
                        
                        // WordPress /wp-admin is normal and has built-in protection
                        $severity = ($path === '/wp-admin') ? 'medium' : 'high';
                        
                        // Reduce severity if rate limiting is present
                        if ($hasRateLimiting) {
                            $severity = ($severity === 'high') ? 'medium' : 'low';
                        }
                        
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'Publicly Accessible Admin Panel',
                            'severity' => $severity,
                            'location' => $url . $path,
                            'evidence' => "Admin login page is publicly accessible at '{$path}'" . 
                                         ($path === '/wp-admin' ? ' (WordPress default)' : '') .
                                         ($hasRateLimiting ? ' - Rate limiting detected ✓' : ' - No rate limiting detected'),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Route not accessible - this is good
            }
        }
    }
    
    /**
     * Test for rate limiting on a URL.
     */
    protected function testRateLimiting(string $url): bool
    {
        try {
            $responses = [];
            
            // Make 5 rapid requests
            for ($i = 0; $i < 5; $i++) {
                $response = Http::timeout(3)->post($url, [
                    'username' => 'test_' . $i,
                    'password' => 'test_' . $i,
                ]);
                $responses[] = $response->status();
                
                // If we get rate limited (429) or blocked, rate limiting exists
                if ($response->status() === 429 || $response->status() === 403) {
                    return true;
                }
            }
            
            // Check if response times increased (soft rate limiting)
            // If all requests succeeded with same status, no rate limiting
            return false;
            
        } catch (\Exception $e) {
            // If requests fail, assume some protection exists
            return true;
        }
    }

    /**
     * Check for directory listing vulnerability.
     */
    protected function checkDirectoryListing(string $url): void
    {
        $directories = [
            '/uploads',
            '/files',
            '/assets',
            '/storage',
        ];

        foreach ($directories as $dir) {
            try {
                $response = Http::timeout(5)->get($url . $dir);
                
                if ($response->successful()) {
                    $body = $response->body();
                    if (str_contains($body, 'Index of') || str_contains($body, 'Directory listing')) {
                        $this->addFinding([
                            'type' => 'runtime',
                            'title' => 'Directory Listing Enabled',
                            'severity' => 'high',
                            'location' => $url . $dir,
                            'evidence' => "Directory listing is enabled for '{$dir}'",
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Directory not accessible - this is good
            }
        }
    }

    /**
     * Check for open dangerous ports.
     */
    protected function checkOpenPorts(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        
        $dangerousPorts = [
            22 => ['name' => 'SSH', 'severity' => 'medium'],
            3306 => ['name' => 'MySQL', 'severity' => 'critical'],
            5432 => ['name' => 'PostgreSQL', 'severity' => 'critical'],
            6379 => ['name' => 'Redis', 'severity' => 'critical'],
            27017 => ['name' => 'MongoDB', 'severity' => 'critical'],
        ];

        foreach ($dangerousPorts as $port => $info) {
            try {
                $output = shell_exec("timeout 2 nc -zv {$host} {$port} 2>&1");
                
                if ($output && (str_contains($output, 'succeeded') || str_contains($output, 'open'))) {
                    $this->addFinding([
                        'type' => 'runtime',
                        'title' => "Open {$info['name']} Port ({$port})",
                        'severity' => $info['severity'],
                        'location' => "{$host}:{$port}",
                        'evidence' => "{$info['name']} port {$port} is publicly accessible",
                    ]);
                }
            } catch (\Exception $e) {
                // Port check failed - skip
            }
        }
    }

    /**
     * Check CORS configuration.
     */
    protected function checkCors(string $url): void
    {
        try {
            $response = Http::withHeaders([
                'Origin' => 'https://evil.com',
            ])->get($url);

            $corsHeader = $response->header('Access-Control-Allow-Origin');
            
            if ($corsHeader === '*') {
                $this->addFinding([
                    'type' => 'runtime',
                    'title' => 'Wildcard CORS Policy',
                    'severity' => 'high',
                    'location' => $url,
                    'evidence' => 'CORS policy allows requests from any origin (*)',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("CORS check failed for {$url}: " . $e->getMessage());
        }
    }

    /**
     * Add a finding to the results array.
     */
    protected function addFinding(array $finding): void
    {
        $this->findings[] = $finding;
    }
}
