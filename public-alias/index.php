<?php
/**
 * Alias entry point for /clinic/public access
 * This file handles requests to /clinic/public/* and forwards them to the main application
 */

// Set the correct base path for URL generation
$_SERVER['SCRIPT_NAME'] = '/clinic/public/index.php';

// Get the requested path after /clinic/public/
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = '/clinic/public-alias';

// Extract the path after the base
if (strpos($requestUri, $basePath) === 0) {
    $path = substr($requestUri, strlen($basePath));
    $_SERVER['REQUEST_URI'] = $path;
} else {
    $_SERVER['REQUEST_URI'] = '/';
}

// Change to the main public directory
chdir(__DIR__ . '/../public');

// Include the main application
require __DIR__ . '/../public/index.php';
