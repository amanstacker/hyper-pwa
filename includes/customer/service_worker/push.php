<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$backend_url = esc_js( HYPWA_PUSH_API_BASE );

return <<<JS


/* Push Notifications */
const backendUrl = '{$backend_url}';

const HYPWA_BADGE_DB = 'hypwa_badge_db';
const HYPWA_BADGE_STORE = 'badge';

function hypwaGetBadgeDB() {
    return new Promise(function(resolve, reject) {
        if (!('indexedDB' in self)) {
            return reject(new Error('IndexedDB not supported'));
        }
        var req = indexedDB.open(HYPWA_BADGE_DB, 1);
        req.onupgradeneeded = function(e) {
            var db = e.target.result;
            if (!db.objectStoreNames.contains(HYPWA_BADGE_STORE)) {
                db.createObjectStore(HYPWA_BADGE_STORE);
            }
        };
        req.onsuccess = function(e) { resolve(e.target.result); };
        req.onerror = function(e) { reject(e.target.error); };
    });
}

function hypwaGetStoredBadge() {
    return hypwaGetBadgeDB().then(function(db) {
        return new Promise(function(resolve) {
            var tx = db.transaction(HYPWA_BADGE_STORE, 'readonly');
            var store = tx.objectStore(HYPWA_BADGE_STORE);
            var req = store.get('count');
            req.onsuccess = function() { resolve(typeof req.result === 'number' ? req.result : 0); };
            req.onerror = function() { resolve(0); };
        });
    }).catch(function() { return 0; });
}

function hypwaSetStoredBadge(count) {
    return hypwaGetBadgeDB().then(function(db) {
        return new Promise(function(resolve) {
            var tx = db.transaction(HYPWA_BADGE_STORE, 'readwrite');
            var store = tx.objectStore(HYPWA_BADGE_STORE);
            var req = store.put(count, 'count');
            req.onsuccess = function() { resolve(count); };
            req.onerror = function() { resolve(count); };
        });
    }).catch(function() { return count; });
}

function hypwaOnPushBadge(explicitCount) {
    if (!('setAppBadge' in navigator)) {
        return Promise.resolve();
    }
    // If In-App Inbox (Premium) is active, it manages badge count based on unread inbox messages
    if (self.hypwaHasInbox) {
        return Promise.resolve();
    }
    if (typeof explicitCount === 'number' && explicitCount > 0) {
        return hypwaSetStoredBadge(explicitCount).then(function() {
            return navigator.setAppBadge(explicitCount);
        }).catch(function() {});
    }

    return Promise.all([
        hypwaGetStoredBadge(),
        (self.registration && typeof self.registration.getNotifications === 'function')
            ? self.registration.getNotifications().then(function(n) { return (n && n.length) ? n.length : 0; }).catch(function() { return 0; })
            : Promise.resolve(0)
    ]).then(function(results) {
        var stored = results[0] || 0;
        var activeNotifs = results[1] || 0;
        var newCount = Math.max(stored + 1, activeNotifs, 1);
        return hypwaSetStoredBadge(newCount).then(function() {
            return navigator.setAppBadge(newCount);
        });
    }).catch(function() {
        return navigator.setAppBadge(1).catch(function() {});
    });
}

function hypwaOnNotificationClickBadge() {
    if (!('setAppBadge' in navigator)) {
        return Promise.resolve();
    }
    if (self.hypwaHasInbox) {
        return Promise.resolve();
    }
    if (self.registration && typeof self.registration.getNotifications === 'function') {
        return self.registration.getNotifications().then(function(notifications) {
            var count = (notifications && notifications.length) ? notifications.length : 0;
            if (count > 0) {
                return hypwaSetStoredBadge(count).then(function() {
                    return navigator.setAppBadge(count);
                });
            } else {
                return hypwaSetStoredBadge(0).then(function() {
                    if ('clearAppBadge' in navigator) {
                        return navigator.clearAppBadge();
                    }
                });
            }
        }).catch(function() {});
    }
    return hypwaSetStoredBadge(0).then(function() {
        if ('clearAppBadge' in navigator) {
            return navigator.clearAppBadge();
        }
    }).catch(function() {});
}

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
    let payload = null;
    try {
        payload = event.data.json();
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
        const explicitCount = (payload && typeof payload.badge_count === 'number' && payload.badge_count > 0) ? payload.badge_count : null;
        badgePromise = showPromise.then(function() {
            return hypwaOnPushBadge(explicitCount);
        });
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

    const badgePromise = hypwaOnNotificationClickBadge();

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
        Promise.all([urlPromise, clickTrackPromise, badgePromise])
    );
});

self.addEventListener('notificationclose', function(event) {
    if ('setAppBadge' in navigator) {
        event.waitUntil(hypwaOnNotificationClickBadge());
    }
});

JS;
