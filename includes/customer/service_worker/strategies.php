<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return <<<'JS'

async function hypwaNetworkFirst(request) {

	const cache = await caches.open(config.cacheName);

	try {

		const response = await fetch(request);

		const notFound = await hypwaGetNotFoundResponse(request, response);

		if (notFound) {
			return notFound;
		}

		if ( hypwaCanCacheResponse(response) ) {
			cache.put(request, response.clone());
		}

		return response;

	} catch (error) {

		const cached = await cache.match(request);

		if (cached) {
			return cached;
		}

		const offline = await hypwaGetOfflineResponse(request);

		if (offline) {
			return offline;
		}

		throw error;

	}

}

async function hypwaCacheFirst(request) {

	const cache = await caches.open(config.cacheName);

	const cached = await cache.match(request);

	if (cached) {
		return cached;
	}

	try {

		const response = await fetch(request);

		const notFound = await hypwaGetNotFoundResponse(request, response);

		if (notFound) {
			return notFound;
		}

		if ( hypwaCanCacheResponse(response) ) {
			cache.put(request, response.clone());
		}

		return response;

	} catch (error) {

		const offline = await hypwaGetOfflineResponse(request);

		if (offline) {
			return offline;
		}

		throw error;

	}

}

async function hypwaStaleWhileRevalidate(request) {

	const cache = await caches.open(config.cacheName);

	const cached = await cache.match(request);

	const networkFetch = fetch(request)
		.then(async (response) => {

			const notFound = await hypwaGetNotFoundResponse(request, response);

			if (notFound) {
				return notFound;
			}

			if ( hypwaCanCacheResponse(response) ) {
				cache.put(request, response.clone());
			}

			return response;

		})
		.catch(async () => {

			const offline = await hypwaGetOfflineResponse(request);

			return offline;

		});

	if (cached) {

		networkFetch;

		return cached;

	}

	const response = await networkFetch;

	if (response) {
		return response;
	}

	throw new Error('Network request failed.');

}

JS;