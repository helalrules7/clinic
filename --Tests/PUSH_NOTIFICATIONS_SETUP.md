# Push Notifications Setup Guide

## Overview
This system now supports **server-side push notifications** that work even when the browser tab is closed or on mobile devices.

## Requirements
1. PHP >= 8.2
2. Composer
3. VAPID keys (already generated)

## Installation Steps

### 1. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 2. Configure VAPID Keys
The VAPID keys are already configured in `.env`:
- `VAPID_PUBLIC_KEY`: Public key (used in client-side JavaScript)
- `VAPID_PRIVATE_KEY`: Private key (used in server-side PHP)

### 3. Set Up Cron Job
Add this to your crontab to check for alerts and send push notifications:

```bash
# Run every minute
* * * * * /usr/bin/php /home/hclinic/web/roaya.hclinic.clinic/public_html/app/Scripts/send_push_notifications.php

# Or run every 5 minutes (recommended)
*/5 * * * * /usr/bin/php /home/hclinic/web/roaya.hclinic.clinic/public_html/app/Scripts/send_push_notifications.php
```

To edit crontab:
```bash
crontab -e
```

### 4. Create Storage Directory
```bash
mkdir -p /home/hclinic/web/roaya.hclinic.clinic/public_html/storage
chmod 755 /home/hclinic/web/roaya.hclinic.clinic/public_html/storage
```

## How It Works

### Client-Side (Browser)
1. User enables push notifications in browser
2. Browser subscription is saved to database
3. Multiple browser subscriptions are supported

### Server-Side (PHP)
1. `AlertController` checks for active alerts
2. When alerts are found, `PushNotificationService` sends push notifications
3. Cron job runs periodically to check for alerts and send notifications

### Service Worker
1. Receives push notifications from server
2. Displays notification even when tab is closed
3. Handles notification clicks and actions

## Testing

### Test Push Notifications
1. Enable push notifications in browser
2. Create an alert that triggers now
3. Check if notification appears (even with tab closed)

### Test Cron Job
```bash
php /home/hclinic/web/roaya.hclinic.clinic/public_html/app/Scripts/send_push_notifications.php
```

## Troubleshooting

### Push Notifications Not Working
1. Check VAPID keys in `.env`
2. Verify composer dependencies are installed
3. Check browser console for errors
4. Verify Service Worker is registered (`/sw.js`)

### Cron Job Not Running
1. Check crontab: `crontab -l`
2. Check cron logs: `/var/log/cron` or `journalctl -u cron`
3. Test script manually: `php app/Scripts/send_push_notifications.php`

### Notifications Not Appearing on Mobile
1. Ensure HTTPS is enabled (required for push notifications)
2. Check browser permissions
3. Verify Service Worker is registered

## Files Modified/Created
- `composer.json`: Added `minishlink/web-push` dependency
- `app/Services/PushNotificationService.php`: Service to send push notifications
- `app/Controllers/AlertController.php`: Modified to send push notifications
- `app/Scripts/send_push_notifications.php`: Cron job script
- `public/sw.js`: Updated to handle server-side push notifications
- `.env`: Added VAPID keys

## Notes
- Push notifications work on all devices (desktop, mobile, tablet)
- Works even when browser tab is closed
- Multiple browser subscriptions are supported
- Invalid subscriptions are automatically removed

