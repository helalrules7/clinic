<?php
/**
 * Secretary page icon bundle — CSS + PHP helpers (§34).
 */
if (!function_exists('sec_dash_icon')) {
    require __DIR__ . '/sec-dash-icons.php';
}
$__secIconsCss = '/app/Views/secretary/assets/css/sec-dash-icons.css';
$__secIconsCssPath = __DIR__ . '/../assets/css/sec-dash-icons.css';
?>
<link href="<?= $__secIconsCss ?>?v=<?= file_exists($__secIconsCssPath) ? filemtime($__secIconsCssPath) : time() ?>" rel="stylesheet">
