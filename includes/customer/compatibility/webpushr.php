<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect the Webpushr plugin by folder path in active_plugins, rather than
 * a class name. Class/namespace internals can get renamed between versions
 * (this bit us on Pushnami) — the plugin's slug on WordPress.org
 * (webpushr-web-push-notifications) is a more stable thing to key off of.
 *
 * @return bool
 */
function hypwa_is_webpushr_plugin_active() {

	foreach ( (array) get_option( 'active_plugins', array() ) as $plugin ) {
		if ( 0 === strpos( $plugin, 'webpushr-web-push-notifications/' ) ) {
			return true;
		}
	}

	if ( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		foreach ( array_keys( (array) $network_plugins ) as $plugin ) {
			if ( 0 === strpos( $plugin, 'webpushr-web-push-notifications/' ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * @return bool
 */
function hypwa_is_webpushr_compat_enabled() {
	return (bool) HYPWA_Options::get( 'comp_webpushr', '0' );
}

add_filter( 'hypwa_service_worker_template', 'hypwa_webpushr_merge_sw_template' );
add_action( 'wp_footer', 'hypwa_webpushr_reassert_sw', 99999 );
add_action( 'admin_init', 'hypwa_webpushr_maybe_auto_enable' );
add_action( 'activated_plugin', 'hypwa_webpushr_maybe_auto_enable' );

/**
 * Prepend Webpushr's worker import so both SDKs run inside one file.
 *
 * @param string $sw Generated service worker JS.
 * @return string
 */
function hypwa_webpushr_merge_sw_template( $sw ) {

	if ( ! hypwa_is_webpushr_plugin_active() || ! hypwa_is_webpushr_compat_enabled() ) {
		return $sw;
	}

	$import = "importScripts('https://cdn.webpushr.com/sw-server.min.js');\n\n";
	return $import . $sw;
}

/**
 * Auto-check Hyper PWA's Webpushr compatibility toggle whenever Webpushr is
 * (or becomes) active. One-time nudge only — tracked via
 * hypwa_webpushr_auto_enabled so a later manual uncheck is respected.
 */
function hypwa_webpushr_maybe_auto_enable() {

	if ( ! hypwa_is_webpushr_plugin_active() ) {
		return;
	}

	if ( hypwa_is_webpushr_compat_enabled() ) {
		return;
	}

	if ( get_option( 'hypwa_webpushr_auto_enabled' ) ) {
		return;
	}

	HYPWA_Options::set( 'comp_webpushr', '1' );
	update_option( 'hypwa_webpushr_auto_enabled', '1' );
}

/**
 * Defensive fallback: if Webpushr's own plugin registers a competing worker
 * at its default path despite Manual Integration, take the scope back over
 * with our merged worker (which already contains its importScripts() call).
 */
function hypwa_webpushr_reassert_sw() {

	if ( ! hypwa_is_webpushr_plugin_active() || ! hypwa_is_webpushr_compat_enabled() ) {
		return;
	}

	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$sw_url = hypwa_sw_url();
	$scope  = trailingslashit( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' );
	?>
<script id="hypwa-webpushr-sw-reassert">
(function() {
	if (!('serviceWorker' in navigator)) return;
	var swUrl = <?php echo wp_json_encode( esc_url_raw( $sw_url ) ); ?>;
	var scope = <?php echo wp_json_encode( esc_url_raw( $scope ) ); ?>;

	function normalizeScope(u) {
		try { return new URL(u, location.href).href.replace(/\/$/, ''); } catch (e) { return u; }
	}
	function sameUrl(a, b) {
		try { return new URL(a, location.href).href === new URL(b, location.href).href; } catch (e) { return false; }
	}
	var targetScope = normalizeScope(scope);

	function takeOverServiceWorker() {
		return navigator.serviceWorker.getRegistrations().then(function(registrations) {
			var pending = [];
			registrations.forEach(function(reg) {
				if (normalizeScope(reg.scope) !== targetScope) return;
				var worker = reg.installing || reg.waiting || reg.active;
				var scriptUrl = worker ? worker.scriptURL : '';
				if (sameUrl(scriptUrl, swUrl)) return;
				pending.push(reg.unregister());
			});
			return Promise.all(pending);
		}).then(function() {
			return navigator.serviceWorker.register(swUrl, { scope: scope });
		}).catch(function() {});
	}

	if (document.readyState === 'complete') {
		takeOverServiceWorker();
	} else {
		window.addEventListener('load', function onLoad() {
			window.removeEventListener('load', onLoad);
			takeOverServiceWorker();
		});
	}

	[50, 200, 800, 2000, 5000].forEach(function(ms) {
		setTimeout(takeOverServiceWorker, ms);
	});

	var reTimer;
	navigator.serviceWorker.addEventListener('controllerchange', function() {
		clearTimeout(reTimer);
		reTimer = setTimeout(function() {
			var c = navigator.serviceWorker.controller;
			if (c && !sameUrl(c.scriptURL, swUrl)) {
				takeOverServiceWorker();
			}
		}, 100);
	});
})();
</script>
	<?php
}