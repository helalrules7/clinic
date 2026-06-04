<?php
/**
 * Global procedural helpers (CodeIgniter-style) — file-scope functions
 * that views and modal partials use without namespace overhead.
 *
 * Loaded once from app/index.php + public/index.php at bootstrap time
 * via require_once. Wrapped in function_exists() so re-includes are safe.
 *
 * Currently provides:
 *   base_url($path = '')   — root-relative URL respecting the app's base path
 *   asset_url($path = '')  — alias of base_url() for clarity at call-sites
 *
 * Both delegate to \App\Lib\UrlHelper::url() so the basePath detection
 * (subdirectory vs. virtual host vs. /clinic/public) stays single-source.
 */

if (!function_exists('base_url')) {
    /**
     * Build a URL relative to the application root.
     *
     * @param string $path  Path appended to the base. Leading slash optional.
     * @return string       Root-relative URL (e.g. "/app/Views/foo.css")
     */
    function base_url(string $path = ''): string
    {
        return \App\Lib\UrlHelper::url($path);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Asset URL — semantic alias of base_url(), used when the intent is
     * specifically "URL of an asset file" rather than a route.
     */
    function asset_url(string $path = ''): string
    {
        return \App\Lib\UrlHelper::url($path);
    }
}
