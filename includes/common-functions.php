<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hypwa_rgb_string_to_hex( $rgb_string ) {
    // Extract numbers from string
    preg_match_all('/\d+/', $rgb_string, $matches);
    if ( count($matches[0]) === 3 ) {
        return hypwa_rgb_to_hex($matches[0][0], $matches[0][1], $matches[0][2]);
    }
    return false;
}

function hypwa_rgb_to_hex( $r, $g, $b ) {
    // Ensure values are within 0–255
    $r = max(0, min(255, intval($r)));
    $g = max(0, min(255, intval($g)));
    $b = max(0, min(255, intval($b)));

    // Convert to hex and return with leading #
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Check whether the site is using HTTPS.
 *
 * @return bool True if the site URL uses HTTPS, false otherwise.
 */
function hypwa_is_https() {

	return is_ssl() || 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );
}

/**
 * Check whether the manifest has the required fields.
 *
 * @return bool True if the manifest is valid, false otherwise.
 */
function hypwa_is_manifest_valid() {

	$app_name   = trim( HYPWA_Options::get( 'app_name' ) );
	$short_name = trim( HYPWA_Options::get( 'app_short_name' ) );

	if ( '' === $app_name || '' === $short_name ) {
		return false;
	}

	$serving_method = HYPWA_Options::get( 'file_serving_method', 'dynamic' );
	if ( 'static' === $serving_method ) {
		$filename = function_exists( 'hypwa_manifest_filename' ) ? hypwa_manifest_filename() : 'hyper-pwa-manifest.json';
		if ( ! file_exists( ABSPATH . $filename ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Check whether the service worker is active (physically present if using static serving).
 *
 * @return bool True if the service worker is active, false otherwise.
 */
function hypwa_is_service_worker_active() {
	$serving_method = HYPWA_Options::get( 'file_serving_method', 'dynamic' );
	if ( 'static' === $serving_method ) {
		$filename = function_exists( 'hypwa_sw_filename' ) ? hypwa_sw_filename() : 'hyper-pwa-sw.js';
		if ( ! file_exists( ABSPATH . $filename ) ) {
			return false;
		}
	}

	return true;
}


/**
 * Check whether the required app icon is configured.
 *
 * @return bool True if the app icon is set, false otherwise.
 */
function hypwa_has_app_icon() {

	return ! empty( HYPWA_Options::get( 'app_icon' ) );
}

/**
 * Check whether an offline page is configured.
 *
 * @return bool True if an offline page is configured, false otherwise.
 */
function hypwa_has_offline_page() {

	return ! empty( HYPWA_Options::get( 'offline_page' ) );
}

/**
 * Check whether the Hyper PWA Premium plugin is active on this site.
 *
 * @return bool
 */
function hypwa_is_pro_plugin_active() {

	return defined( 'HYPWAP_VERSION' );
}

/**
 * Check whether the Hyper PWA Premium license is active/valid.
 *
 * Reads the 'hypwa_license_data' option that the Pro plugin's license.php
 * stores the EDD license-check response in. Works even when the Pro plugin
 * itself isn't loaded, since it only reads a wp_option.
 *
 * @return bool
 */
function hypwa_is_pro_license_active() {

	$license_data = get_option( 'hypwa_license_data' );

	return ! empty( $license_data['license'] ) && 'valid' === $license_data['license'];
}

/**
 * Check whether the Pro plugin is active but its license is not yet activated.
 *
 * @return bool
 */
function hypwa_is_pro_license_inactive_notice_needed() {

	return hypwa_is_pro_plugin_active() && ! hypwa_is_pro_license_active();
}

/**
 * Get list of iOS devices for splash screen generation.
 *
 * @return array
 */
function hypwa_get_ios_devices() {
	return [
		[ 'w' => 430, 'h' => 932, 'dpr' => 3, 'media' => '(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 932, 'h' => 430, 'dpr' => 3, 'media' => '(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 393, 'h' => 852, 'dpr' => 3, 'media' => '(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 852, 'h' => 393, 'dpr' => 3, 'media' => '(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 428, 'h' => 926, 'dpr' => 3, 'media' => '(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 926, 'h' => 428, 'dpr' => 3, 'media' => '(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 390, 'h' => 844, 'dpr' => 3, 'media' => '(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 844, 'h' => 390, 'dpr' => 3, 'media' => '(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 375, 'h' => 812, 'dpr' => 3, 'media' => '(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 812, 'h' => 375, 'dpr' => 3, 'media' => '(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 414, 'h' => 896, 'dpr' => 3, 'media' => '(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 896, 'h' => 414, 'dpr' => 3, 'media' => '(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 414, 'h' => 896, 'dpr' => 2, 'media' => '(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 896, 'h' => 414, 'dpr' => 2, 'media' => '(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
		[ 'w' => 414, 'h' => 736, 'dpr' => 3, 'media' => '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)' ],
		[ 'w' => 736, 'h' => 414, 'dpr' => 3, 'media' => '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)' ],
		[ 'w' => 375, 'h' => 667, 'dpr' => 2, 'media' => '(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 667, 'h' => 375, 'dpr' => 2, 'media' => '(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
		[ 'w' => 1024, 'h' => 1366, 'dpr' => 2, 'media' => '(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 1366, 'h' => 1024, 'dpr' => 2, 'media' => '(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
		[ 'w' => 834, 'h' => 1194, 'dpr' => 2, 'media' => '(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 1194, 'h' => 834, 'dpr' => 2, 'media' => '(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
		[ 'w' => 820, 'h' => 1180, 'dpr' => 2, 'media' => '(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 1180, 'h' => 820, 'dpr' => 2, 'media' => '(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
		[ 'w' => 810, 'h' => 1080, 'dpr' => 2, 'media' => '(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)' ],
		[ 'w' => 1080, 'h' => 810, 'dpr' => 2, 'media' => '(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)' ],
	];
}