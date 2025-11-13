<?php

namespace App\Lib;

class UrlHelper
{
    private static $basePath = null;
    
    /**
     * Get the base path for the application
     */
    public static function getBasePath()
    {
        if (self::$basePath === null) {
            // Check different deployment scenarios
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $serverName = $_SERVER['SERVER_NAME'] ?? '';
            
            // Debug: Log the values for troubleshooting
            error_log("UrlHelper Debug - SCRIPT_NAME: $scriptName, REQUEST_URI: $requestUri, SERVER_NAME: $serverName");
            
            // Scenario 1: Apache alias /clinic/public access
            if (strpos($scriptName, '/clinic/public/') !== false) {
                self::$basePath = '/clinic/public';
            }
            // Scenario 2: Request URI contains /clinic/public
            elseif (strpos($requestUri, '/clinic/public') !== false) {
                self::$basePath = '/clinic/public';
            }
            // Scenario 3: Virtual host with DocumentRoot = /clinic/public (IP-based direct access)
            elseif ($serverName === '192.168.40.128' && (strpos($scriptName, '/index.php') !== false || $scriptName === '/index.php')) {
                self::$basePath = '';
            }
            // Scenario 4: Default case
            else {
                self::$basePath = '';
            }
            
            error_log("UrlHelper Debug - Final basePath: " . self::$basePath);
        }
        
        return self::$basePath;
    }
    
    /**
     * Generate URL with proper base path
     */
    public static function url($path = '')
    {
        $basePath = self::getBasePath();
        
        // Ensure path starts with /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $basePath . $path;
    }
    
    /**
     * Redirect to URL with proper base path
     */
    public static function redirect($path = '', $statusCode = 302)
    {
        $url = self::url($path);
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Get current URL
     */
    public static function current()
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }
    
    /**
     * Check if running in subdirectory
     */
    public static function isSubdirectory()
    {
        return self::getBasePath() !== '';
    }
}
