// resources/js/app.js (atau public/js/app.js, atau langsung di blade)

import './bootstrap.js';  
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// VAPID public key langsung dari Blade (env Laravel)
const publicVapidKey = window.vapidPublicKey;

if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', async () => {
        try {
            // Register service worker dari root
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            console.log('Service Worker registered with scope:', registration.scope);

            // Cek subscription yang sudah ada di origin saat ini
            const existingSub = await registration.pushManager.getSubscription();

            if (existingSub) {
                console.log('Sudah subscribe di origin ini:', location.origin);
                await sendSubscriptionToServer(existingSub);
            } else {
                // Minta izin notifikasi
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    // Subscribe baru
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
                    });

                    console.log('Subscribe berhasil di origin:', location.origin);
                    await sendSubscriptionToServer(subscription);
                } else {
                    console.warn('Izin notifikasi ditolak oleh user');
                }
            }
        } catch (error) {
            console.error('Error saat handle push subscription:', error);
        }
    });
}

// Fungsi kirim subscription ke Laravel
async function sendSubscriptionToServer(subscription) {
    try {
        const response = await fetch('/webpush/subscribe', {  // sesuaikan dengan route kamu
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',  // langsung dari Blade
                'Accept': 'application/json'
            },
            body: JSON.stringify(subscription.toJSON())
        });

        if (response.ok) {
            console.log('Subscription berhasil dikirim ke server');
        } else {
            const errorText = await response.text();
            console.error('Gagal kirim subscription:', response.status, errorText);
        }
    } catch (err) {
        console.error('Fetch error saat kirim subscription:', err);
    }
}

// Helper convert VAPID key base64 ke Uint8Array
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}