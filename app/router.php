<?php
/**
 * Router for PHP built-in server
 */

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// If requesting /public/, redirect to the actual application
if (preg_match('/^\/public\/?(.*)$/', $path, $matches)) {
    $subPath = $matches[1];
    
    // Set up environment for the application
    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    $_SERVER['REQUEST_URI'] = '/' . $subPath;
    $_SERVER['PHP_SELF'] = '/public/index.php';
    
    // Change to public directory
    $originalDir = getcwd();
    chdir(__DIR__ . '/public');
    
    // Include the application
    include __DIR__ . '/public/index.php';
    
    // Restore original directory
    chdir($originalDir);
    return true;
}

// Handle internal application redirects and API calls
if (preg_match('/^\/(doctor|secretary|admin|login|api|logout|print)/', $path)) {
    // Set up environment for the application
    $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['PHP_SELF'] = '/public/index.php';
    
    // Change to public directory
    $originalDir = getcwd();
    chdir(__DIR__ . '/public');
    
    // Include the application
    include __DIR__ . '/public/index.php';
    
    // Restore original directory
    chdir($originalDir);
    return true;
}

// If requesting root, show index.html
if ($path === '/' || $path === '') {
    if (file_exists(__DIR__ . '/index.html')) {
        include __DIR__ . '/index.html';
        return true;
    }
}

// Check if it's a static file in the root
$file = __DIR__ . $path;
if (file_exists($file) && is_file($file)) {
    return false; // Let PHP serve the file
}

// For all other requests, return 404
http_response_code(404);
echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        h1 { color: #e74c3c; }
        p { color: #666; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The requested page could not be found.</p>
    <a href='/'>← Back to Home</a>
</body>
</html>";
return true;
?>
