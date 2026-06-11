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

// Digit normalizer — loaded early for search / phone validation across controllers.
require_once __DIR__ . '/DigitNormalizer.php';

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

if (!function_exists('avatar_thumb')) {
    /**
     * v12_perf: return a small, cached, square WebP thumbnail URL for a profile
     * image so the sidebar/dock/header avatars don't download the full upload
     * (avatars are stored at up to 2048×2048 / ~800 KB but displayed at ≤200 px).
     *
     * The thumbnail is generated on first use (GD) and cached next to the
     * original under a `thumbs/` sub-folder; subsequent loads are a static file.
     * The thumb URL is derived from the original's URL (same directory) so it
     * inherits whatever docroot mapping already serves the original. Returns the
     * ORIGINAL path unchanged on any problem (missing file, no GD, write fail,
     * non-local image) — so it can never break an avatar.
     *
     * @param string|null $publicPath  the URL path already used for the original <img src>
     * @param int         $size        square thumbnail edge in px (clamped 32–512)
     */
    function avatar_thumb(?string $publicPath, int $size = 96): ?string
    {
        if (empty($publicPath)) return $publicPath;
        // Only touch our own user uploads; pass through gravatar/CDN/data URIs etc.
        if (strpos($publicPath, '/uploads/users/') === false) return $publicPath;
        if (!function_exists('imagecreatefromstring')) return $publicPath; // no GD

        $size = max(32, min(512, $size));

        // URL path → filesystem path under public/. The view prefixes '/public'
        // for the URL; the actual file lives at <project>/public/uploads/users/…
        $rel = $publicPath;
        if (strpos($rel, '/public/') === 0) $rel = substr($rel, 7); // drop leading '/public'
        $rel = '/' . ltrim($rel, '/');
        $publicRoot = realpath(__DIR__ . '/../../public');
        if ($publicRoot === false) return $publicPath;
        $srcFs = realpath($publicRoot . $rel);
        // existence + path-traversal guard (must resolve inside public/uploads/users)
        if ($srcFs === false || strpos($srcFs, $publicRoot . '/uploads/users/') !== 0 || !is_file($srcFs)) {
            return $publicPath;
        }

        // NOTE: output JPEG (not WebP). public/uploads/.htaccess only serves an
        // extension allowlist (jpg|jpeg|png|gif|svg|pdf|doc|docx) and 403s the
        // rest — a .webp thumb under uploads/ would be forbidden.
        $info      = pathinfo($srcFs);
        $thumbName = $info['filename'] . '_t' . $size . '.jpg';
        $thumbDir  = $info['dirname'] . '/thumbs';
        $thumbFs   = $thumbDir . '/' . $thumbName;
        $thumbUrl  = rtrim(dirname($publicPath), '/') . '/thumbs/' . $thumbName;

        // Serve cached thumb if it exists and is not older than the source.
        if (is_file($thumbFs) && @filemtime($thumbFs) >= @filemtime($srcFs)) {
            return $thumbUrl;
        }

        $data = @file_get_contents($srcFs);
        if ($data === false) return $publicPath;
        $img = @imagecreatefromstring($data);
        if (!$img) return $publicPath;
        $w = imagesx($img); $h = imagesy($img);
        if ($w < 1 || $h < 1) { imagedestroy($img); return $publicPath; }

        if (!is_dir($thumbDir)) @mkdir($thumbDir, 0775, true);
        if (!is_dir($thumbDir)) { imagedestroy($img); return $publicPath; }

        // Center cover-crop to a square, then scale to $size.
        $side = min($w, $h);
        $sx = (int) (($w - $side) / 2);
        $sy = (int) (($h - $side) / 2);
        $dst = imagecreatetruecolor($size, $size);
        // white matte under the photo (JPEG has no alpha; harmless for opaque sources)
        imagefilledrectangle($dst, 0, 0, $size, $size, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $img, 0, 0, $sx, $sy, $size, $size, $side, $side);
        $ok = @imagejpeg($dst, $thumbFs, 85);
        imagedestroy($img);
        imagedestroy($dst);

        return $ok ? $thumbUrl : $publicPath;
    }
}
