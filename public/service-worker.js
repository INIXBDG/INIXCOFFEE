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

// self.addEventListener('fetch', function(event) {
//     event.respondWith(fetch(event.request));
// });

self.addEventListener('fetch', function(event) {
    // Deteksi permintaan dokumen HTML
    if (event.request.mode === 'navigate' || (event.request.method === 'GET' && event.request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            // Strategi Network First
            fetch(event.request).then(function(networkResponse) {
                return networkResponse;
            }).catch(function(error) {
                // Jatuh kembali (fallback) ke cache hanya jika jaringan luring penuh
                return caches.match(event.request);
            })
        );
    } else {
        // Pertahankan logika caching aset statis (CSS, JS, Gambar) yang sudah ada sebelumnya di sini
    }
});
