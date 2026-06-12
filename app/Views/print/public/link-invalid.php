<?php
/**
 * Neutral failure page for PublicShareController::deny().
 * Identical for every failure reason (invalid / expired / revoked / not-found /
 * unavailable / rate-limited) so it never reveals which token state was hit.
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>الرابط غير متاح</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #070B14; color: #F8FAFC; padding: 24px;
            font-family: "Cairo", "Segoe UI", Tahoma, sans-serif;
            background-image: radial-gradient(900px 500px at 50% -10%, rgba(99,102,241,0.20), transparent 60%);
        }
        .box {
            background: #131A29; border: 1px solid #334155; border-radius: 18px;
            padding: 34px 28px; max-width: 420px; text-align: center;
            box-shadow: 0 20px 56px rgba(0,0,0,.55);
        }
        .icon {
            width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 30px;
            background: rgba(99,102,241,0.16); border: 1px solid rgba(129,140,248,.35);
        }
        h1 { font-size: 20px; margin: 0 0 10px; color: #818CF8; font-weight: 800; }
        p { margin: 0; color: #94A3B8; font-size: 15px; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🔒</div>
        <h1>الرابط غير متاح</h1>
        <p>هذا الرابط غير صالح أو انتهت صلاحيته.<br>برجاء التواصل مع العيادة للحصول على رابط جديد.</p>
    </div>
</body>
</html>
