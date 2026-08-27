(function() {
    'use strict';

    if (typeof hypwa_push_config === 'undefined') {
        return;
    }

    const siteId = hypwa_push_config.site_id;
    const backendUrl = hypwa_push_config.backend_url || "https://hyperpushx.com";

    if (!siteId || siteId.trim() === '') {
        console.warn('Hyper PWA Push: Missing Website ID.');
        return;
    }

    const STORAGE_KEY_SUB_ID = 'hypwa_push_subscriber_id';

    class HyperPWAPushSDK {
        constructor() {
            this.siteId = siteId;
            this.backendUrl = backendUrl;
            this.subscriberId = localStorage.getItem(STORAGE_KEY_SUB_ID);
            this.init();
        }

        async init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                return;
            }

            // Retrieve permission status
            if (Notification.permission === 'granted') {
                await this.setupSubscription();
            } else if (Notification.permission === 'default') {
                this.showOptInBanner();
            }
        }

        showOptInBanner() {
            if (localStorage.getItem('hypwa_push_prompt_dismissed') === 'true') {
                return;
            }

            // Create styles
            const style = document.createElement('style');
            style.innerHTML = `
                #hypwa-push-prompt-container {
                    position: fixed;
                    left: 20px;
                    bottom: calc(20px + env(safe-area-inset-bottom, 0px));
                    z-index: 999998;
                    max-width: 360px;
                    width: calc(100% - 40px);
                    padding: 16px;
                    background: #1a1a2e;
                    color: #ffffff;
                    border-radius: 14px;
                    border: 0.5px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    font-size: 14px;
                    line-height: 1.5;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    transform: translateY(16px);
                    opacity: 0;
                    visibility: hidden;
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease, visibility 0.25s ease;
                }
                #hypwa-push-prompt-container.hypwa-is-visible {
                    transform: translateY(0);
                    opacity: 1;
                    visibility: visible;
                }
                .hypwa-push-btn-allow {
                    background: #2563eb;
                    border: none;
                    padding: 6px 16px;
                    font-size: 13px;
                    font-weight: 600;
                    color: #ffffff;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: background-color 0.2s;
                }
                .hypwa-push-btn-allow:hover {
                    background-color: #1d4ed8;
                }
                .hypwa-push-btn-no {
                    background: none;
                    border: none;
                    padding: 6px 12px;
                    font-size: 13px;
                    font-weight: 600;
                    color: #94a3b8;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: color 0.2s;
                }
                .hypwa-push-btn-no:hover {
                    color: #ffffff;
                }
                @media (max-width: 480px) {
                    #hypwa-push-prompt-container {
                        left: 12px;
                        bottom: calc(12px + env(safe-area-inset-bottom, 0px));
                        width: calc(100% - 24px);
                        max-width: none;
                        padding: 14px;
                        gap: 10px;
                    }
                }
            `;
            document.head.appendChild(style);

            // Create banner markup
            const container = document.createElement('div');
            container.id = 'hypwa-push-prompt-container';
            container.innerHTML = `
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #3b82f6; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <strong style="display: block; font-size: 14px; font-weight: 600; margin: 0 0 3px; color: #ffffff;">Get Latest Updates!</strong>
                        <span style="font-size: 12.5px; color: #94a3b8; display: block; line-height: 1.4;">Subscribe to get notified about our latest posts.</span>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button class="hypwa-push-btn-no">No Thanks</button>
                    <button class="hypwa-push-btn-allow">Allow</button>
                </div>
            `;
            document.body.appendChild(container);

            // Animate in
            setTimeout(() => {
                container.classList.add('hypwa-is-visible');
            }, 1000);

            // Handle actions
            const dismiss = () => {
                container.classList.remove('hypwa-is-visible');
                setTimeout(() => {
                    container.remove();
                    style.remove();
                }, 300);
            };

            container.querySelector('.hypwa-push-btn-no').addEventListener('click', () => {
                localStorage.setItem('hypwa_push_prompt_dismissed', 'true');
                dismiss();
            });

            container.querySelector('.hypwa-push-btn-allow').addEventListener('click', async () => {
                dismiss();
                try {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        await this.setupSubscription();
                    } else {
                        localStorage.setItem('hypwa_push_prompt_dismissed', 'true');
                    }
                } catch (err) {
                    console.error('Hyper PWA Push: Permission request failed:', err);
                }
            });
        }

        async setupSubscription() {
            try {
                // Fetch subscription config (VAPID key)
                const configResponse = await fetch(`${this.backendUrl}/api/v1/sdk/subscribe-config?site_id=${this.siteId}`);
                if (!configResponse.ok) {
                    throw new Error('Failed to load VAPID config');
                }
                const config = await configResponse.json();
                const vapidPublicKey = config.vapid_public_key;

                if (!vapidPublicKey) {
                    throw new Error('VAPID public key not found');
                }

                // Get SW registration
                const registration = await navigator.serviceWorker.ready;
                
                // Subscribe
                const convertedVapidKey = this.urlB64ToUint8Array(vapidPublicKey);
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });

                // Extract keys
                const p256dh = btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh'))));
                const auth = btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth'))));

                // Send subscription payload to backend
                const payload = {
                    site_id: this.siteId,
                    endpoint: subscription.endpoint,
                    public_key: p256dh,
                    auth_token: auth,
                    browser: this.getBrowserName(),
                    os: this.getOSName(),
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    language: navigator.language || 'en'
                };

                const subResponse = await fetch(`${this.backendUrl}/api/v1/sdk/subscribe`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!subResponse.ok) {
                    throw new Error('Subscription upload failed');
                }

                const result = await subResponse.json();
                this.subscriberId = result.subscriber.id;
                localStorage.setItem(STORAGE_KEY_SUB_ID, this.subscriberId);

                console.log('Hyper PWA Push: Successfully subscribed to push notifications.');
            } catch (err) {
                console.error('Hyper PWA Push: Subscription failed:', err);
            }
        }

        urlB64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        getBrowserName() {
            const ua = navigator.userAgent;
            if (ua.indexOf('Chrome') > -1) return 'Chrome';
            if (ua.indexOf('Firefox') > -1) return 'Firefox';
            if (ua.indexOf('Safari') > -1) return 'Safari';
            if (ua.indexOf('Edge') > -1) return 'Edge';
            return 'Other';
        }

        getOSName() {
            const appVersion = navigator.appVersion;
            if (appVersion.indexOf('Win') > -1) return 'Windows';
            if (appVersion.indexOf('Mac') > -1) return 'MacOS';
            if (appVersion.indexOf('Linux') > -1) return 'Linux';
            if (appVersion.indexOf('Android') > -1) return 'Android';
            if (appVersion.indexOf('like Mac') > -1) return 'iOS';
            return 'Other';
        }
    }

    // Initialize SDK on load
    if (document.readyState === 'complete') {
        new HyperPWAPushSDK();
    } else {
        window.addEventListener('load', () => {
            new HyperPWAPushSDK();
        });
    }
})();
