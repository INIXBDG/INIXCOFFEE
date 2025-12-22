import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY ?? "local",
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? "127.0.0.1",
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    forceTLS: false,
    scheme: "http",
    enabledTransports: ["ws"],
    disableStats: true,
    authEndpoint: "/broadcasting/auth",
    auth: {
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
            "Accept": "application/json",
        },
    },
});

console.log("Echo SIAP! Menghubungkan ke ws://127.0.0.1:6001");