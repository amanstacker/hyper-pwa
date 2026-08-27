<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return <<<'JS'

self.addEventListener( 'fetch', ( event ) => {

	// Cache only GET requests.
	if ( event.request.method !== 'GET' ) {
		return;
	}

	// Ignore unsupported protocols.
	if ( ! /^https?:$/i.test( new URL( event.request.url ).protocol ) ) {
		return;
	}

	// Skip external resources unless enabled.
	if ( ! config.cache_external && new URL( event.request.url ).origin !== self.location.origin ) {
		return;
	}

	// Ignore partial content requests (video/audio/PDF streaming).
	if ( event.request.headers.has( 'range' ) ) {
		return;
	}

	// Skip excluded URLs.
	if ( config.exclude_from_caching_status && ! hypwaCanCacheRequest( event.request.url )) {
		console.log( 'Hyper PWA: Current request is excluded from cache.' );
		return;
	}

	// Runtime caching disabled / Caching Strategies is turned off.
	if ( ! config.caching_status ) {
		const isSameOrigin = new URL( event.request.url ).origin === self.location.origin;
		const acceptHeader = event.request.headers.get( 'Accept' ) || '';
		const isHtml = acceptHeader.includes( 'text/html' ) || event.request.mode === 'navigate';

		if ( config.link_hover_prefetch === '1' && isSameOrigin && isHtml ) {
			// Allow hover prefetching to bypass caching_status check
		} else {
			return;
		}
	}

	event.respondWith(
		(async () => {

			const strategy = hypwaGetStrategy(event.request);

			switch (strategy) {

				case 'cache_first':
					return hypwaCacheFirst(event.request);

				case 'stale_while_revalidate':
					return hypwaStaleWhileRevalidate(event.request);

				case 'network_only':
					return fetch(event.request);

				case 'network_first':
				default:
					return hypwaNetworkFirst(event.request);

			}

		})()
	);
});

JS;