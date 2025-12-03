<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// HACKEX: Force upload limits for built-in PHP server
// The built-in server doesn't read .user.ini files, so we set them here
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '60M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
