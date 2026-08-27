/**
 * Hyper PWA - iOS & Safari Compatibility Install Prompt
 */
(function() {
    'use strict';

    // Helper to check if user is on iOS / iPadOS
    function getMobileOS() {
        const ua = window.navigator.userAgent;
        const isIOSDevice = /iPhone|iPad|iPod/.test(ua);
        const isIPadOS = (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        return isIOSDevice || isIPadOS;
    }

    // Helper to check if already running in standalone mode
    function isStandaloneMode() {
        return ('standalone' in window.navigator) && window.navigator.standalone;
    }

    // Initialize prompt
    function initIOSPrompt(force = false) {
        // Only run on iOS devices in non-standalone (browser) mode
        if ( ! getMobileOS() || isStandaloneMode() ) {
            return;
        }

        // Check if dismissed in the last 7 days (bypass if forced)
        if (!force) {
            const dismissedTime = localStorage.getItem('hypwa_ios_prompt_dismissed');
            if ( dismissedTime ) {
                const now = new Date().getTime();
                const daysSinceDismissed = (now - parseInt(dismissedTime, 10)) / (1000 * 60 * 60 * 24);
                if ( daysSinceDismissed < 7 ) {
                    return;
                }
            }
        }

        // Prevent duplicates
        let existingContainer = document.querySelector('.hypwa-ios-prompt-container');
        if (existingContainer) {
            existingContainer.remove();
        }

        // Construct HTML
        const container = document.createElement('div');
        container.className = 'hypwa-ios-prompt-container';

        // Retrieve local variables passed via wp_localize_script
        const appName = typeof hypwa_ios_prompt !== 'undefined' ? hypwa_ios_prompt.app_name : document.title;
        const appIcon = typeof hypwa_ios_prompt !== 'undefined' ? hypwa_ios_prompt.app_icon : '';
        const titleText = typeof hypwa_ios_prompt !== 'undefined' && hypwa_ios_prompt.title ? hypwa_ios_prompt.title : 'Add ' + appName + ' to Home Screen';
        const descText = typeof hypwa_ios_prompt !== 'undefined' && hypwa_ios_prompt.desc ? hypwa_ios_prompt.desc : 'Install this app on your device for offline support and quick access.';
        const step1Text = typeof hypwa_ios_prompt !== 'undefined' && hypwa_ios_prompt.step1 ? hypwa_ios_prompt.step1 : 'Tap the Share button in the browser toolbar.';
        const step2Text = typeof hypwa_ios_prompt !== 'undefined' && hypwa_ios_prompt.step2 ? hypwa_ios_prompt.step2 : 'Scroll down and select Add to Home Screen.';

        let iconHTML = '';
        if ( appIcon ) {
            iconHTML = `<img class="hypwa-ios-prompt-app-icon" src="${appIcon}" alt="${appName}">`;
        }

        const shareIconSVG = `<span class="hypwa-ios-prompt-icon-inline"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50"><path d="M30,13.5V17c0,0.55-0.45,1-1,1s-1-0.45-1-1v-4.5c0-0.55,0.45-1,1-1h11.5c0.55,0,1,0.45,1,1v16.5c0,0.55-0.45,1-1,1S39.5,29,39.5,28V13.5H30z"/><path d="M19,30H7.5c-0.55,0-1-0.45-1-1V12.5c0-0.55,0.45-1,1-1H19c0.55,0,1,0.45,1,1V17c0,0.55,0.45,1,1,1s1-0.45,1-1v-5.5c0-0.55-0.45-1-1-1H6.5c-0.55,0-1,0.45-1,1v18.5c0,0.55,0.45,1,1,1H19c0.55,0,1-0.45,1-1s-0.45-1-1-1V30z"/><path d="M24,4c-0.55,0-1,0.45-1,1v23c0,0.55,0.45,1,1,1s1-0.45,1-1V5C25,4.45,24.55,4,24,4z"/><path d="M17.35,11.35c0.39,0.39,1.02,0.39,1.41,0L24,6.17l5.23,5.18c0.39,0.39,1.02,0.39,1.41,0s0.39-1.02,0-1.41l-5.94-5.88c-0.39-0.39-1.02-0.39-1.41,0l-5.94,5.88C16.96,10.33,16.96,10.96,17.35,11.35z"/></svg></span>`;
        
        let step1HTML = step1Text;
        if ( step1HTML.indexOf('[share_icon]') !== -1 ) {
            step1HTML = step1HTML.replace('[share_icon]', shareIconSVG);
        } else if ( step1HTML.indexOf('Share button') !== -1 ) {
            step1HTML = step1HTML.replace('Share button', 'Share button ' + shareIconSVG);
        }

        container.innerHTML = `
            <div class="hypwa-ios-prompt-card" id="hypwa-ios-card">
                <button class="hypwa-ios-prompt-close" id="hypwa-ios-close" aria-label="Dismiss">&times;</button>
                <div class="hypwa-ios-prompt-header">
                    ${iconHTML}
                    <div class="hypwa-ios-prompt-title-block">
                        <h4>${titleText}</h4>
                        <p>${descText}</p>
                    </div>
                </div>
                <div class="hypwa-ios-prompt-body">
                    <div class="hypwa-ios-prompt-step">
                        <span class="hypwa-ios-prompt-step-number">1</span>
                        <div class="hypwa-ios-prompt-step-text">
                            ${step1HTML}
                        </div>
                    </div>
                    <div class="hypwa-ios-prompt-step">
                        <span class="hypwa-ios-prompt-step-number">2</span>
                        <div class="hypwa-ios-prompt-step-text">
                            ${step2Text}
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(container);

        // Event listener to dismiss
        const closeBtn = document.getElementById('hypwa-ios-close');
        const card = document.getElementById('hypwa-ios-card');

        closeBtn.addEventListener('click', function() {
            card.classList.add('hypwa-dismissed');
            localStorage.setItem('hypwa_ios_prompt_dismissed', new Date().getTime().toString());
            
            // Remove from DOM after transition completes
            setTimeout(() => {
                container.remove();
            }, 350);
        });
    }

    // Expose global helper to programmatically trigger iOS prompt
    window.hypwaShowIOSPrompt = function() {
        initIOSPrompt(true);
    };

    // Run on DOM loaded
    if ( document.readyState === 'loading' ) {
        document.addEventListener('DOMContentLoaded', function() { initIOSPrompt(); });
    } else {
        initIOSPrompt();
    }
})();
