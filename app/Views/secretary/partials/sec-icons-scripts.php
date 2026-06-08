<?php
if (!function_exists('sec_dash_icons_registry')) {
    require __DIR__ . '/sec-dash-icons.php';
}
$__secIconsJs = '/app/Views/secretary/assets/js/sec-dash-icons.js';
$__secIconsJsPath = __DIR__ . '/../assets/js/sec-dash-icons.js';
?>
<script type="application/json" id="secDashIconRegistry"><?= json_encode(sec_dash_icons_registry(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= $__secIconsJs ?>?v=<?= file_exists($__secIconsJsPath) ? filemtime($__secIconsJsPath) : time() ?>"></script>
