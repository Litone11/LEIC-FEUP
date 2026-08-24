import { showNotificationToast } from './notification_toast';

let subscribed = false;
let bound = false;

export function setupRealtimeNotifications() {
    if (subscribed) return;

    const meta = document.querySelector('meta[name="user-id"]');
    if (!meta?.content) {
        console.warn('No valid user id');
        return;
    }

    const userId = meta.content;

    if (!window.Echo?.connector?.pusher?.connection) {
        console.warn('Echo not ready');
        return;
    }

    const connection = window.Echo.connector.pusher.connection;

    const subscribe = () => {
        if (subscribed) return;
        subscribed = true;

        console.log('Subscribing to user channel', userId);

        window.Echo
            .private(`user.${userId}`)
            .listen('.notification-created', (e) => {
                console.log('Notification received', e);

                showNotificationToast('You have a new notification');

                const list = document.querySelector('#notifications-list');
                if (list) {
                    list.insertAdjacentHTML('afterbegin', e.notification_card);
                }
            });
    };

    if (connection.state === 'connected') {
        subscribe();
    } else if (!bound) {
        bound = true;
        console.log('Waiting for Pusher connection...');
        connection.bind('connected', subscribe);
    }
}
