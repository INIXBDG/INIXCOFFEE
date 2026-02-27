self.addEventListener('install', function(event) {
    console.log('[Service Worker] Installing');
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    console.log('[Service Worker] Activating');
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            self.registration.navigationPreload.enable()
        ])
    );
});

self.addEventListener('push', function(event) {
    let data;
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        console.error('Invalid JSON payload', e);
        data = { title: 'Notifikasi Sistem', body: 'Ada update baru' };
    }

    const title = data.title || 'Notifikasi Baru';
    const options = {
        body: data.body || 'Klik untuk melihat detail',
        icon: data.icon || '/icons/icon-192x192.png',
        badge: data.badge || '/icons/badge-96x96.png',
        data: data.data || {},
        actions: data.actions || [],
        vibrate: data.vibrate || [200, 100, 200],
        requireInteraction: data.requireInteraction || false,
        tag: data.tag || 'notif-' + Date.now(),
        renotify: data.renotify !== false,
        silent: data.silent || false,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click received');
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function(clientList) {
                const url = event.notification.data?.path || '/';
                const fullUrl = new URL(url, self.location.origin).href;

                if (clientList.length > 0) {
                    let webClient = clientList.find(c => 'focus' in c);
                    if (webClient) {
                        webClient.focus();
                        if (event.action) {
                            webClient.postMessage({
                                action: event.action,
                                notification: event.notification
                            });
                        }
                        return;
                    }
                }

                if (event.action === 'view' || !event.action) {
                    return clients.openWindow(fullUrl);
                }
            })
    );
});

self.addEventListener('notificationclose', function(event) {
    console.log('[Service Worker] Notification closed');
});