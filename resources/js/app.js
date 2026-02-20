import './bootstrap.js';  

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(registration) {
                console.log('ServiceWorker registered with scope:', registration.scope);
            })
            .catch(function(error) {
                console.error('ServiceWorker registration failed:', error);
            });
    });
}