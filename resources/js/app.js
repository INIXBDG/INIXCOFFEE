import './bootstrap.js';
import './modules/global-validator.js';
import './modules/compress-validator.js';
import './modules/notifications-sweetalert.js';

const publicVapidKey =
    window.vapidPublicKey ||
    document.querySelector('meta[name="vapid-public-key"]')?.content;
const csrfToken =
    window.csrfToken ||
    document.querySelector('meta[name="csrf-token"]')?.content ||
    '';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            if ('PushManager' in window && publicVapidKey) {
                const existingSub = await registration.pushManager.getSubscription();
                if (existingSub) {
                    await sendSubscriptionToServer(existingSub);
                } else if (Notification.permission === 'default') {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(publicVapidKey),
                        });
                        await sendSubscriptionToServer(subscription);
                    }
                }
            }
        } catch (error) {
            console.warn('Service Worker / Push registration skipped:', error);
        }
    });
}

async function sendSubscriptionToServer(subscription) {
    try {
        const subscribeUrl = window.subscribeUrl || '/webpush/subscribe';
        await fetch(subscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify(subscription.toJSON()),
        });
    } catch (err) {
        console.warn('Fetch error saat kirim subscription:', err);
    }
}

export function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

window.urlBase64ToUint8Array = urlBase64ToUint8Array;
