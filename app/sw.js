// Service Worker for Push Notifications
const CACHE_NAME = 'clinic-push-v1';

// Install event
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    let notificationData = {
        title: 'Alert',
        body: 'You have a new alert',
        icon: '/assets/images/Light.png',
        badge: '/assets/images/Light.png',
        tag: 'alert-notification',
        requireInteraction: false,
        data: {}
    };

    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = {
                title: data.title || 'Alert',
                body: data.body || data.message || 'You have a new alert',
                icon: data.icon || '/assets/images/Light.png',
                badge: data.badge || '/assets/images/Light.png',
                tag: data.tag || `alert-${data.alert_id || Date.now()}`,
                requireInteraction: data.requireInteraction || false,
                data: data.data || {},
                actions: data.actions || []
            };
        } catch (e) {
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            data: notificationData.data,
            actions: notificationData.actions,
            vibrate: [200, 100, 200],
            timestamp: Date.now()
        })
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const notificationData = event.notification.data || {};
    const action = event.action;
    
    let urlToOpen = '/doctor/alerts';
    
    // Handle action buttons
    if (action === 'view' && notificationData.url) {
        urlToOpen = notificationData.url;
    } else if (action === 'dismiss' && notificationData.alert_id) {
        // Dismiss the alert
        fetch('/api/alerts/dismiss', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ alert_id: notificationData.alert_id })
        }).catch(err => console.error('Failed to dismiss alert:', err));
        
        // Still open the alerts page
        urlToOpen = '/doctor/alerts';
    } else if (notificationData.url) {
        urlToOpen = notificationData.url;
    }

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((clientList) => {
            // Check if there's already a window/tab open
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    // Focus existing window and navigate to URL
                    client.focus();
                    if (client.navigate && client.url !== urlToOpen) {
                        client.navigate(urlToOpen);
                    }
                    return;
                }
            }
            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

