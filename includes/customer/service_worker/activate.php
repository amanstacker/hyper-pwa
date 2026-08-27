<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return <<<'JS'

self.addEventListener( 'activate', ( event ) => {
	event.waitUntil(
		(async () => {
			const cacheNames = await caches.keys();

			await Promise.all(
				cacheNames
					.filter( ( cacheName ) => cacheName !== config.cacheName )
					.map( ( cacheName ) => caches.delete( cacheName ) )
			);

			await self.clients.claim();
		})()
	);
});

JS;