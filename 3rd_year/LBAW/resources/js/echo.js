import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (!pusherKey) {
    console.warn('[echo] Missing VITE_PUSHER_APP_KEY; skipping realtime setup.');
} else {
    window.Echo = new Echo({
        broadcaster: "pusher",
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true,
        wsHost: import.meta.env.VITE_PUSHER_HOST,
        wsPort: import.meta.env.VITE_PUSHER_PORT,
        wssPort: import.meta.env.VITE_PUSHER_PORT,
        enabledTransports: ["ws", "wss"],
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('Pusher connected');
    });

    window.Echo.connector.pusher.connection.bind('error', err => {
        console.error('Pusher error', err);
    });
}
