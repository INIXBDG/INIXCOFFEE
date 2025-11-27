import './bootstrap'; 

import Echo from 'laravel-echo';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'local',                   // sama dengan REVERB_APP_KEY di .env
    wsHost: '127.0.0.1',            // localhost
    wsPort: 8080,                   // default Reverb
    wssPort: 8080,
    forceTLS: false,                // penting untuk local
    enabledTransports: ['ws'],      // paksa pakai WebSocket biasa
    disableStats: true
});

console.log('Laravel Reverb + Echo berhasil di-load!');