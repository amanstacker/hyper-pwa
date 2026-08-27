<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect the OneSignal plugin (v2 class API or v3+ constants).
 *
 * @return bool
 */
function hypwa_is_onesignal_plugin_active() {

	if ( class_exists( 'OneSignal' ) ) {
		return true;
	}

	if ( defined( 'ONESIGNAL_PLUGIN_URL' ) || defined( 'ONESIGNAL_PLUGIN_DIR' ) ) {
		return true;
	}

	return false;
}

/**
 * @return bool
 */
function hypwa_is_onesignal_compat_enabled() {
	return (bool) HYPWA_Options::get( 'comp_one_signal', '0' );
}

/**
 * NOTE: these are registered unconditionally, on every load. The
 * OneSignal-active / toggle-enabled check happens INSIDE each callback,
 * not around the add_filter/add_action call. This matters: hypwa_sw_filename
 * and hypwa_service_worker_template must already be hooked before
 * hypwa_register_rewrite_rules() runs on `init`, and registering the hook
 * itself behind another `init` callback makes that an unreliable priority
 * race. Always-on registration with an internal guard sidesteps the race
 * entirely, since PHP attaches these the moment this file is loaded
 * (during plugin bootstrap), well before `init` fires.
 */
add_filter( 'hypwa_sw_filename', 'hypwa_onesignal_sw_filename' );
add_filter( 'hypwa_service_worker_template', 'hypwa_onesignal_merge_sw_template' );
add_action( 'init', 'hypwa_onesignal_maybe_use_custom_manifest', 20 );
add_action( 'wp_footer', 'hypwa_onesignal_reassert_sw', 99999 );
add_action( 'admin_init', 'hypwa_onesignal_maybe_auto_enable' );
add_action( 'activated_plugin', 'hypwa_onesignal_maybe_auto_enable' );

/**
 * Auto-check Hyper PWA's OneSignal compatibility toggle whenever OneSignal
 * is (or becomes) active, so the user isn't required to also flip a second
 * switch manually. This only forces the toggle on ONCE per site (tracked via
 * hypwa_onesignal_auto_enabled) — after that, the user's own choice in the
 * Compatibility settings tab (including turning it back off) is respected.
 * Runs on `admin_init` as a safety net for "OneSignal already active before
 * Hyper PWA checked", and on `activated_plugin` for the moment OneSignal
 * itself gets activated.
 */
function hypwa_onesignal_maybe_auto_enable() {
 
	if ( ! hypwa_is_onesignal_plugin_active() ) {
		return;
	}
 
	if ( hypwa_is_onesignal_compat_enabled() ) {
		return;
	}
 
	if ( get_option( 'hypwa_onesignal_auto_enabled' ) ) {
		return;
	}
 
	HYPWA_Options::set( 'comp_one_signal', '1' );
	update_option( 'hypwa_onesignal_auto_enabled', '1' );
}

/**
 * Rename the generated SW file to match what OneSignal's SDK looks for.
 *
 * @param string $filename Original filename.
 * @return string
 */
function hypwa_onesignal_sw_filename( $filename ) {

	if ( ! hypwa_is_onesignal_plugin_active() || ! hypwa_is_onesignal_compat_enabled() ) {
		return $filename;
	}

	return 'OneSignalSDKUpdaterWorker' . hypwa_filename_postfix() . '.js';
}

/**
 * Prepend the OneSignal worker import so both SDKs run inside one file.
 *
 * @param string $sw Generated service worker JS.
 * @return string
 */
function hypwa_onesignal_merge_sw_template( $sw ) {

	if ( ! hypwa_is_onesignal_plugin_active() || ! hypwa_is_onesignal_compat_enabled() ) {
		return $sw;
	}

	$import = "importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');\n\n";
	return $import . $sw;
}

/**
 * Point the OneSignal plugin's manifest option at ours, if the site hasn't
 * already customised it.
 */
function hypwa_onesignal_maybe_use_custom_manifest() {

	if ( ! hypwa_is_onesignal_plugin_active() || ! hypwa_is_onesignal_compat_enabled() ) {
		return;
	}

	$os_settings = get_option( 'OneSignalWPSetting' );

	if ( empty( $os_settings['custom_manifest_url'] ) && empty( $os_settings['use_custom_manifest'] ) ) {

		$os_settings['use_custom_manifest'] = true;
		$os_settings['custom_manifest_url'] = esc_url( hypwa_manifest_url() );

		update_option( 'OneSignalWPSetting', $os_settings );
	}
}

/**
 * Restore OneSignal's manifest option when Hyper PWA is deactivated, so
 * OneSignal goes back to serving its own manifest.
 */
function hypwa_onesignal_register_deactivation_hook() {

	static $registered = false;

	if ( $registered || ! class_exists( 'OneSignal' ) ) {
		return;
	}
	$registered = true;

	register_deactivation_hook(
		HYPWA_DIR_NAME_FILE,
		function () {
			$os_settings = get_option( 'OneSignalWPSetting' );
			if ( is_array( $os_settings ) ) {
				$os_settings['use_custom_manifest'] = false;
				update_option( 'OneSignalWPSetting', $os_settings );
			}
		}
	);
}
add_action( 'plugins_loaded', 'hypwa_onesignal_register_deactivation_hook', 30 );

/**
 * On the front end, unregister any other service worker at our scope
 * (e.g. OneSignal's own default worker registration) and (re)register our
 * merged OneSignal + Hyper PWA worker.
 */
function hypwa_onesignal_reassert_sw() {

	if ( ! hypwa_is_onesignal_plugin_active() || ! hypwa_is_onesignal_compat_enabled() ) {
		return;
	}

	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$sw_url = hypwa_sw_url();
	$scope  = trailingslashit( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' );
	?>
<script id="hypwa-onesignal-sw-reassert">
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
