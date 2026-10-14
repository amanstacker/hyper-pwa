<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$backend_url = esc_js( HYPWA_PUSH_API_BASE );

return <<<JS


/* Push Notifications */
const backendUrl = '{$backend_url}';
self.addEventListener('push', function(event) {
    if (!event.data) {
        console.warn('HyperPush SW: Push event received but has no data payload.');
        return;
    }
    let title = 'Notification';
    let options = {
        body: '',
        data: { url: '/' }
    };
    try {
        const payload = event.data.json();
        title = payload.title || 'Notification';
        options.body = payload.message || '';
        if (payload.icon) options.icon = payload.icon;
        if (payload.image) options.image = payload.image;
        if (payload.badge) options.badge = payload.badge;
        options.requireInteraction = payload.require_interaction || false;
        options.actions = payload.actions || [];
        options.data = {
            url: payload.url || '/',
            campaign_id: payload.campaign_id || null,
            subscriber_id: payload.subscriber_id || null
        };
    } catch (err) {
        console.error('HyperPush SW: Failed to parse push notification payload:', err);
    }

    const showPromise = self.registration.showNotification(title, options);
    let badgePromise = Promise.resolve();

    if ('setAppBadge' in navigator) {
        const badgeCount = (payload && typeof payload.badge_count === 'number') ? payload.badge_count : 1;
        badgePromise = navigator.setAppBadge(badgeCount).catch(function() {});
    }

    // Notify any open foreground app pages in real-time
    const broadcastPromise = (self.clients && self.clients.matchAll) ? self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
        for (let i = 0; i < windowClients.length; i++) {
            try {
                windowClients[i].postMessage({
                    source: 'hypwa_sw',
                    type: 'INBOX_UPDATED',
                    payload: payload
                });
            } catch (e) {}
        }
    }).catch(function() {}) : Promise.resolve();

    event.waitUntil(
        Promise.all([showPromise, badgePromise, broadcastPromise])
    );
});

self.addEventListener('notificationclick', function(event) {
    const notification = event.notification;
    const action = event.action;
    notification.close();

    if ('clearAppBadge' in navigator) {
        navigator.clearAppBadge().catch(function() {});
    }

    const data = notification.data || {};
    let targetUrl = data.url || '/';
    if (action) {
        const matchingAction = (notification.actions || []).find(act => act.action === action);
        if (matchingAction && matchingAction.url) {
            targetUrl = matchingAction.url;
        }
    }
    const urlPromise = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(function(windowClients) {
        for (let i = 0; i < windowClients.length; i++) {
            const client = windowClients[i];
            if (client.url === targetUrl && 'focus' in client) {
                return client.focus();
            }
        }
        if (clients.openWindow) {
            return clients.openWindow(targetUrl);
        }
    });
    let clickTrackPromise = Promise.resolve();
    if (data.subscriber_id) {
        const trackEndpoint = `\${backendUrl}/api/v1/sdk/click-track`;
        clickTrackPromise = fetch(trackEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                campaign_id: data.campaign_id,
                subscriber_id: data.subscriber_id
            })
        }).catch(err => {
            console.error('HyperPush SW: Failed to send click tracking event:', err);
        });
    }
    event.waitUntil(
        Promise.all([urlPromise, clickTrackPromise])
    );
});

JS;
