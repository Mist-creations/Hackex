<?php

/**
 * HACKEX Custom Server Router
 * 
 * This file is used with PHP's built-in server to ensure proper routing
 * and to verify upload limits are correctly configured.
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Verify upload limits are set correctly
$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');

// Log current settings for debugging
error_log("HACKEX Server - upload_max_filesize: {$uploadMax}, post_max_size: {$postMax}");

// If the request is for a static file that exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // Serve the file directly
}

// Otherwise, route to Laravel's index.php
require_once __DIR__ . '/public/index.php';
