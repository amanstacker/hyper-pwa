<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return <<<'JS'

/**
 * Check whether the current request should be cached.
 *
 * @param {string} url Request URL.
 * @return {boolean}
 */

function hypwaCanCacheRequest( url ) {	
	
	return ! config.exclude_from_caching.some(
		( pattern ) => url.includes( pattern )
	);
}

function hypwaGetStrategy(request) {
	
	const destination = request.destination;

	if (destination === 'document') {
		return config.page_cache_strategy;
	}

	if (
		destination === 'style' ||
		destination === 'script' ||
		destination === 'font'
	) {
		return config.sa_cache_strategy;
	}

	if (destination === 'image') {
		return config.image_cache_strategy;
	}

	return 'network_first';
}

async function hypwaGetOfflineResponse(request) {

	if (
		! config.offline_page ||
		request.mode !== 'navigate'
	) {
		return null;
	}

	return await caches.match(config.offline_page);

}

async function hypwaGetNotFoundResponse(request, response) {

	if (
		! config.not_found_page ||
		request.mode !== 'navigate' ||
		response.status !== 404
	) {
		return null;
	}

	return await caches.match(config.not_found_page);

}

function hypwaCanCacheResponse(response) {

	if (
		config.cache_external &&
		( response.type === 'cors' || response.type === 'opaque' )
	) {
		return true;
	}

	if ( ! response.ok ) {
		return false;
	}

	if ( response.type === 'basic' ) {
		return true;
	}

	return false;

}

JS;