<?php
/**
 * PHI-free Open Graph card served to social/link-preview crawlers
 * (WhatsApp, Facebook, Twitter, …) by PublicShareController::visitDocuments().
 * Contains ONLY clinic branding — never any patient or clinical data.
 *
 * Vars: $clinic, $og
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$clinicName = $clinic['name'] ?? 'مركز رؤية';
$og = $og ?? [];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $e($og['title'] ?? 'تقرير الزيارة') ?></title>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $e($og['site'] ?? $clinicName) ?>">
    <meta property="og:title" content="<?= $e($og['title'] ?? 'تقرير الزيارة') ?>">
    <meta property="og:description" content="<?= $e($og['desc'] ?? 'تقرير زيارتك الطبي') ?>">
    <?php if (!empty($og['image'])): ?>
    <meta property="og:image" content="<?= $e($og['image']) ?>">
    <meta property="og:image:alt" content="<?= $e($clinicName) ?>">
    <?php endif; ?>
    <?php if (!empty($og['url'])): ?><meta property="og:url" content="<?= $e($og['url']) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $e($og['title'] ?? 'تقرير الزيارة') ?>">
    <meta name="twitter:description" content="<?= $e($og['desc'] ?? 'تقرير زيارتك الطبي') ?>">
    <?php if (!empty($og['image'])): ?><meta name="twitter:image" content="<?= $e($og['image']) ?>"><?php endif; ?>
</head>
<body>
    <h1><?= $e($clinicName) ?></h1>
    <p>تقرير الزيارة الطبي</p>
</body>
</html>
