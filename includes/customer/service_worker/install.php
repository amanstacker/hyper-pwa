<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return <<<'JS'

self.addEventListener('install', function(event) {

	// Pre-caching disabled or no URLs to cache.
	if (
		! config.pre_caching_status ||
		! Array.isArray(config.pre_caching) ||
		! config.pre_caching.length
	) {
		return;
	}

	event.waitUntil(
		caches.open(config.cacheName).then(function(cache) {
			return Promise.all(
				config.pre_caching.map(function(url) {
					return cache.add(url).catch(function(error) {
						if (config.debug) {
							console.warn('Failed to precache:', url, error);
						}
					});
				})
			);
		})
	);
});

JS;