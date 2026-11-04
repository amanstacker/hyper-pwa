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
	const isCacheExternal = ( config.cache_external === '1' || config.cache_external === 1 || config.cache_external === true );
	if ( ! isCacheExternal && new URL( event.request.url ).origin !== self.location.origin ) {
		return;
	}

	// Ignore partial content requests (video/audio/PDF streaming).
	if ( event.request.headers.has( 'range' ) ) {
		return;
	}

	// Skip excluded URLs.
	const isExcludeStatus = ( config.exclude_from_caching_status === '1' || config.exclude_from_caching_status === 1 || config.exclude_from_caching_status === true );
	if ( isExcludeStatus && ! hypwaCanCacheRequest( event.request.url ) ) {
		return;
	}

	// Runtime caching disabled / Caching Strategies is turned off.
	const isCachingEnabled = ( config.caching_status === '1' || config.caching_status === 1 || config.caching_status === true );
	if ( ! isCachingEnabled ) {
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