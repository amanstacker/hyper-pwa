// Capture browser's install prompt event without blocking default browser prompt
window.addEventListener('beforeinstallprompt', (e) => {
	window.hypwaDeferredPrompt = e;
});

if ( 'serviceWorker' in navigator ) {

	window.addEventListener( 'load', () => {

		navigator.serviceWorker
			.register(
				hypwa_sw.sw_url,
				{
					scope: hypwa_sw.scope
				}
			)
			.then( ( registration ) => {

				console.log(
					'Hyper PWA: Service Worker registered',
					registration.scope
				);

			} )
			.catch( ( error ) => {

				console.error(
					'Hyper PWA: Service Worker registration failed',
					error
				);

			} );

		if ( hypwa_sw.connectivity_notices_status && typeof hypwaConnectivityNotice === 'function' ) {
			hypwaConnectivityNotice();
		}
		
		if ( hypwa_sw.custom_install_app_status && typeof hypwaInstallPrompt === 'function' ) {
			hypwaInstallPrompt();
		}

		if ( hypwa_sw.initial_loader_status && typeof hypwaInitialLoader === 'function' ) {
			hypwaInitialLoader();
		}
		
		if ( hypwa_sw.bn_status && typeof hypwaBnBuild === 'function' ) {
			hypwaBnBuild();
		}

		// Bind custom install triggers
		if ( typeof hypwa_sw !== 'undefined' && hypwa_sw.custom_install_trigger ) {
			hypwaBindCustomInstallTriggers();
		}

	} );

}

function hypwaBindCustomInstallTriggers() {
	const triggerStr = hypwa_sw.custom_install_trigger;
	if ( ! triggerStr ) {
		return;
	}

	const selectors = triggerStr.split(',').map(s => s.trim()).filter(Boolean);
	if ( selectors.length === 0 ) {
		return;
	}

	// Use event delegation on document.body for dynamic elements
	document.body.addEventListener('click', (event) => {
		for (const selector of selectors) {
			const target = event.target.closest(selector);
			if (target) {
				event.preventDefault();
				hypwaTriggerPWAInstall();
				break;
			}
		}
	});
}

function hypwaTriggerPWAInstall() {
	if ( window.hypwaDeferredPrompt ) {
		window.hypwaDeferredPrompt.prompt();
		window.hypwaDeferredPrompt.userChoice.then((choiceResult) => {
			if (choiceResult.outcome === 'accepted') {
				console.log('Hyper PWA: PWA installed successfully.');
			}
			window.hypwaDeferredPrompt = null;
		});
	} else if ( typeof window.hypwaShowIOSPrompt === 'function' ) {
		window.hypwaShowIOSPrompt();
	} else {
		const msg = hypwa_sw.install_unsupported_msg || 'PWA installation is not supported on this browser/device, or the app is already installed.';
		alert(msg);
	}
}