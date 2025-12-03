<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class StaticScanner
{
    protected array $findings = [];
    protected string $extractPath;
    protected ?string $zipPath = null;

    /**
     * Scan uploaded ZIP file for security vulnerabilities.
     */
    public function scan(string $zipPath): array
    {
        $this->findings = [];
        $this->extractPath = storage_path('app/scans/' . uniqid('scan_'));
        $this->zipPath = storage_path('app/' . $zipPath); // Store full path for deletion

        try {
            // Extract ZIP file
            $this->extractZip($zipPath);

            // Run all static checks
            $this->checkForSecrets();
            $this->checkForEnvFiles();
            $this->checkForDebugFlags();
            $this->checkForPrivateKeys();
            $this->checkForDatabaseDumps();
            $this->checkForSensitiveLogs();
            $this->checkForHardcodedPasswords();

            // Cleanup extracted files AND delete uploaded ZIP
            $this->cleanup();

            return $this->findings;
        } catch (\Exception $e) {
            Log::error("Static scan failed: " . $e->getMessage());
            $this->cleanup();
            throw $e;
        }
    }

    /**
     * Extract ZIP file to temporary directory.
     */
    protected function extractZip(string $zipPath): void
    {
        $fullPath = storage_path('app/' . $zipPath);
        
        // Debug logging
        Log::info("Attempting to extract ZIP", [
            'relative_path' => $zipPath,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'is_readable' => is_readable($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 'N/A',
        ]);
        
        $zip = new ZipArchive();
        $openResult = $zip->open($fullPath);
        
        Log::info("ZIP open result", [
            'result' => $openResult,
            'result_bool' => $openResult === true,
        ]);
        
        if ($openResult === true) {
            // Check extraction size to prevent zip bombs
            $totalSize = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $totalSize += $stat['size'];
                
                if ($totalSize > 100 * 1024 * 1024) { // 100MB limit
                    throw new \Exception('ZIP file too large when extracted');
                }
            }

            File::makeDirectory($this->extractPath, 0755, true);
            $zip->extractTo($this->extractPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to open ZIP file');
        }
    }

    /**
     * Check for hardcoded API keys and secrets.
     */
    protected function checkForSecrets(): void
    {
        $patterns = [
            'AWS Access Key' => '/AKIA[0-9A-Z]{16}/',
            'AWS Secret Key' => '/aws_secret_access_key\s*=\s*[\'"]?([a-zA-Z0-9\/+]{40})[\'"]?/i',
            'OpenAI API Key' => '/sk-[a-zA-Z0-9]{48}/',
            'Stripe API Key' => '/sk_live_[a-zA-Z0-9]{24,}/',
            'GitHub Token' => '/ghp_[a-zA-Z0-9]{36}/',
            'Generic API Key' => '/api[_-]?key[\'"\s:=]+[\'"]?([a-zA-Z0-9_\-]{20,})[\'"]?/i',
            'Generic Secret' => '/secret[\'"\s:=]+[\'"]?([a-zA-Z0-9_\-]{20,})[\'"]?/i',
        ];

        $this->scanFilesForPatterns($patterns, 'critical', 'Hardcoded API Key or Secret');
    }

    /**
     * Check for exposed .env files.
     */
    protected function checkForEnvFiles(): void
    {
        $envFiles = File::glob($this->extractPath . '/**/.env*');
        
        foreach ($envFiles as $file) {
            $relativePath = str_replace($this->extractPath, '', $file);
            $content = File::get($file);

            // Check for sensitive keys in .env
            $sensitiveKeys = ['DB_PASSWORD', 'API_KEY', 'SECRET', 'PASSWORD', 'TOKEN'];
            $foundKeys = [];

            foreach ($sensitiveKeys as $key) {
                if (str_contains($content, $key)) {
                    $foundKeys[] = $key;
                }
            }

            if (!empty($foundKeys)) {
                $this->addFinding([
                    'type' => 'static',
                    'title' => 'Exposed Environment File',
                    'severity' => 'critical',
                    'location' => $relativePath,
                    'evidence' => 'Found .env file with sensitive keys: ' . implode(', ', $foundKeys),
                ]);
            }
        }
    }

    /**
     * Check for debug mode enabled.
     */
    protected function checkForDebugFlags(): void
    {
        $patterns = [
            'Laravel Debug' => '/APP_DEBUG\s*=\s*true/i',
            'Django Debug' => '/DEBUG\s*=\s*True/i',
            'Node Debug' => '/NODE_ENV\s*=\s*[\'"]?development[\'"]?/i',
        ];

        $this->scanFilesForPatterns($patterns, 'high', 'Debug Mode Enabled');
    }

    /**
     * Check for private RSA/SSH keys.
     */
    protected function checkForPrivateKeys(): void
    {
        $keyFiles = array_merge(
            File::glob($this->extractPath . '/**/*.pem'),
            File::glob($this->extractPath . '/**/*.key'),
            File::glob($this->extractPath . '/**/id_rsa')
        );

        foreach ($keyFiles as $file) {
            $relativePath = str_replace($this->extractPath, '', $file);
            $content = File::get($file);

            if (str_contains($content, 'PRIVATE KEY')) {
                $this->addFinding([
                    'type' => 'static',
                    'title' => 'Private Key File Found',
                    'severity' => 'critical',
                    'location' => $relativePath,
                    'evidence' => 'Private RSA/SSH key file detected',
                ]);
            }
        }
    }

    /**
     * Check for database dumps.
     */
    protected function checkForDatabaseDumps(): void
    {
        $sqlFiles = File::glob($this->extractPath . '/**/*.sql');

        foreach ($sqlFiles as $file) {
            $relativePath = str_replace($this->extractPath, '', $file);
            $size = File::size($file);

            if ($size > 1024) { // Larger than 1KB
                $this->addFinding([
                    'type' => 'static',
                    'title' => 'Database Dump File Found',
                    'severity' => 'high',
                    'location' => $relativePath,
                    'evidence' => 'SQL database dump file detected (' . round($size / 1024, 2) . ' KB)',
                ]);
            }
        }
    }

    /**
     * Check for sensitive information in logs.
     */
    protected function checkForSensitiveLogs(): void
    {
        $logFiles = array_merge(
            File::glob($this->extractPath . '/**/*.log'),
            File::glob($this->extractPath . '/**/logs/**/*')
        );

        $sensitivePatterns = [
            'password',
            'api_key',
            'secret',
            'token',
            'credit_card',
        ];

        foreach ($logFiles as $file) {
            if (!is_file($file)) continue;

            $relativePath = str_replace($this->extractPath, '', $file);
            $content = strtolower(File::get($file));

            foreach ($sensitivePatterns as $pattern) {
                if (str_contains($content, $pattern)) {
                    $this->addFinding([
                        'type' => 'static',
                        'title' => 'Sensitive Data in Log Files',
                        'severity' => 'medium',
                        'location' => $relativePath,
                        'evidence' => "Log file may contain sensitive information ('{$pattern}')",
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * Check for hardcoded passwords.
     */
    protected function checkForHardcodedPasswords(): void
    {
        $patterns = [
            'Hardcoded Password' => '/password\s*=\s*[\'"]([^\'"]{6,})[\'"]/',
            'Database Password' => '/db_password\s*=\s*[\'"]([^\'"]+)[\'"]/',
        ];

        $this->scanFilesForPatterns($patterns, 'high', 'Hardcoded Password');
    }

    /**
     * Scan files for regex patterns.
     */
    protected function scanFilesForPatterns(array $patterns, string $severity, string $title): void
    {
        $files = File::allFiles($this->extractPath);

        foreach ($files as $file) {
            // Skip binary files and large files
            if ($file->getSize() > 1024 * 1024) continue; // Skip files > 1MB

            $extension = $file->getExtension();
            $textExtensions = ['php', 'js', 'py', 'env', 'json', 'yml', 'yaml', 'txt', 'md', 'config'];
            
            if (!in_array($extension, $textExtensions)) continue;

            try {
                $content = File::get($file->getPathname());
                $relativePath = str_replace($this->extractPath, '', $file->getPathname());

                foreach ($patterns as $patternName => $pattern) {
                    if (preg_match($pattern, $content, $matches)) {
                        $evidence = isset($matches[1]) ? substr($matches[1], 0, 50) . '...' : 'Pattern detected';
                        
                        $this->addFinding([
                            'type' => 'static',
                            'title' => $title . ' (' . $patternName . ')',
                            'severity' => $severity,
                            'location' => $relativePath,
                            'evidence' => $evidence,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Skip files that can't be read
            }
        }
    }

    /**
     * Add a finding to the results array.
     */
    protected function addFinding(array $finding): void
    {
        $this->findings[] = $finding;
    }

    /**
     * Clean up extracted files.
     */
    protected function cleanup(): void
    {
        // Delete extracted directory
        if (File::exists($this->extractPath)) {
            File::deleteDirectory($this->extractPath);
        }
        
        // Delete uploaded ZIP file for security
        if ($this->zipPath && File::exists($this->zipPath)) {
            File::delete($this->zipPath);
            Log::info("Deleted uploaded ZIP file: {$this->zipPath}");
        }
    }
}
