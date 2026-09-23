function hypwaLog( ...args ) {
    if ( typeof hypwa_sw !== 'undefined' && hypwa_sw.browser_console_logs === '0' ) {
        return;
    }
    console.log( ...args );
}

document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.hypwa-install-btn-shortcode');
    if (!buttons.length) {
        return;
    }

    hypwaLog('Hyper PWA Button: Shortcode button found on page. Checking PWA installation status...');

    // Check if running inside standalone PWA display mode
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    var isAlreadyInstalled = localStorage.getItem('hypwa_installed') === 'true';

    if (isStandalone) {
        hypwaLog('Hyper PWA Button: Hidden because user is browsing inside the standalone PWA app.');
        return;
    }
    if (isAlreadyInstalled) {
        hypwaLog('Hyper PWA Button: Hidden because localStorage flags the app as already installed.');
        return;
    }

    // Function to reveal all button instances on page
    function showAllButtons() {
        hypwaLog('Hyper PWA Button: Event fired. Revealing install buttons.');
        buttons.forEach(function(btn) {
            btn.style.display = 'inline-block';
        });
    }

    // 1. Android/Chrome/Desktop beforeinstallprompt check
    if (window.hypwaDeferredPrompt) {
        hypwaLog('Hyper PWA Button: beforeinstallprompt already fired. Showing button.');
        showAllButtons();
    } else {
        hypwaLog('Hyper PWA Button: Waiting for beforeinstallprompt event from browser...');
        window.addEventListener('beforeinstallprompt', function() {
            showAllButtons();
        });
    }

    // 2. iOS Safari check: Show it immediately since they must be prompted via custom guidelines click
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    if (isIOS && isSafari) {
        showAllButtons();
    }

    // 3. Delegate click events to standard hypwaTriggerPWAInstall
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof hypwaTriggerPWAInstall === 'function') {
                hypwaTriggerPWAInstall();
            } else {
                console.warn('Hyper PWA: hypwaTriggerPWAInstall is not defined.');
            }
        });
    });

    // 4. Listen for appinstalled event to hide button instantly
    window.addEventListener('appinstalled', function() {
        buttons.forEach(function(btn) {
            btn.style.display = 'none';
        });
        localStorage.setItem('hypwa_installed', 'true');
    });
});
