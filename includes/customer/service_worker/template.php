<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safe loader for service worker modules.
 */
function hypwa_sw_load_module( $file ) {
	$content = include $file;

	return is_string( $content ) ? $content : '';
}

function hypwa_service_worker_template() {

	$cache_name = sprintf(
		'%s-hyper-pwa-%d-%s',
		str_replace( '.', '-', wp_parse_url( home_url(), PHP_URL_HOST ) ),
		get_current_blog_id(),
		HYPWA_Options::get( 'force_update' )
	);

	$offline_page = hypwa_convert_url_to_https( get_permalink( (int) HYPWA_Options::get( 'offline_page' ) ) );
	$_404_page = hypwa_convert_url_to_https( get_permalink( (int) HYPWA_Options::get( '404_page' ) ) );
	
	$config = [

		'cacheName'               => $cache_name,	

		'caching_status'          => HYPWA_Options::get( 'cf_caching_status' ),
		'page_cache_strategy'     => HYPWA_Options::get( 'cf_page_cache_strategy' ),
		'sa_cache_strategy'  	  => HYPWA_Options::get( 'cf_static_assets_cache_strategy' ),
		'image_cache_strategy'    => HYPWA_Options::get( 'cf_image_cache_strategy' ),
		'pre_caching_status'      => HYPWA_Options::get( 'cf_pre_caching_status' ),
		'pre_caching'    => apply_filters( 'hypwa_precache_urls', [
			$offline_page,
			$_404_page
		] ),	

		'offline_page'      => $offline_page,
		'not_found_page'    => $_404_page,
	    'offline_message'   => HYPWA_Options::get( 'offline_message'),
		'max_age'           => HYPWA_Options::get( 'cache_max_age', 7 ),
		'max_entries'       => HYPWA_Options::get( 'cache_max_entries', 200 ),
		'max_size_mb'       => HYPWA_Options::get( 'cache_max_size', 50 ),
		'cache_external'       => HYPWA_Options::get( 'cache_external' ),

		'runtime_cache' => [
			'html'  => true,
			'css'   => true,
			'js'    => true,
			'img'   => true,
			'fonts' => true,
			'api'   => false,
		],

		'strategies' => [
			'html'  => 'network-first',
			'css'   => 'stale-while-revalidate',
			'js'    => 'stale-while-revalidate',
			'img'   => 'cache-first',
			'fonts' => 'cache-first',
			'api'   => 'network-first',
		],

		'update' => [
			'skip_waiting'     => true,
			'clients_claim'    => true,
			'broadcast_update' => true,
		],

	    'background_sync' => [
	        'enabled' => HYPWA_Options::get( 'bg_sync', false ),
	        'tags'    => [ 'form-submit', 'analytics' ],
	    ],
		'exclude_from_caching_status'      => HYPWA_Options::get( 'cf_exclude_from_caching_status' ),
		'exclude_from_caching' => apply_filters( 'hypwa_exclude_from_caching', array_values( array_merge(
		    [
		        '/wp-admin/',
		        '/wp-login.php',
		        '/wp-json/',
		        'admin-ajax.php',
		        'xmlrpc.php',
		        '/checkout/',
		        '/cart/',
		        '/my-account/',
		        'wc-ajax=',
		    ],
		    hypwa_get_excluded_caching_urls()
		) ) ),

		'smart_cache' => [
	        'query_strings' => false,
	        'cookies'       => false,
	        'logged_in'     => false,
	        'post_requests' => false,
	    ],

		'fingerprint' => [
			'enabled' => true,
			'ignore_query_keys' => [ 'utm_source', 'utm_medium', 'utm_campaign', 'ref' ],
		],

		'network_awareness' => [
			'enabled' => true,
			'prefer_cache_on_slow_network' => true,
		],

		'gc' => [
			'enabled' => true,
			'max_age_days' => 7,
		],

		'observability' => [
			'cache_hit_tracking' => true,
			'offline_usage_tracking' => true,
		],
	];
	
	$sw  = 'const config = ' . wp_json_encode( $config ) . ";\n\n";

	$base = HYPWA_PLUGIN_DIR_PATH . 'includes/customer/service_worker/';

	$sw .= hypwa_sw_load_module( $base . 'helpers.php' );
	$sw .= hypwa_sw_load_module( $base . 'strategies.php' );	

	$sw .= hypwa_sw_load_module( $base . 'install.php' );
	$sw .= hypwa_sw_load_module( $base . 'activate.php' );
	$sw .= hypwa_sw_load_module( $base . 'fetch.php' );

	if ( '1' === HYPWA_Options::get( 'cf_push_status' ) && '1' === HYPWA_Options::get( 'cf_push_connected' ) && ! class_exists( 'HyperPushX' ) ) {
		$sw .= hypwa_sw_load_module( $base . 'push.php' );
	}

	return apply_filters( 'hypwa_service_worker_template', $sw );
}