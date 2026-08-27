<?php 
// Exit if accessed directly
if ( ! defined('ABSPATH') ) exit;

function hypwa_manifest_template() {

	$home_path = trailingslashit(
		wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/'
	);

	$manifest = [
		'name'        => HYPWA_Options::get( 'app_name' ),
		'short_name'  => HYPWA_Options::get( 'app_short_name' ),
		'description' => HYPWA_Options::get( 'app_description' ),
		'start_url'   => apply_filters( 'hypwa_manifest_start_url', hypwa_get_start_url() ),
		'scope'       => $home_path,
		'display'     => HYPWA_Options::get( 'launch_mode' ),
		'theme_color'      => HYPWA_Options::get( 'theme_color' ),
		'background_color' => HYPWA_Options::get( 'background_color' ),
		'orientation' => HYPWA_Options::get( 'orientation' ),
		'icons'       => [],
	];

	if ( HYPWA_Options::get( 'app_icon' ) ) {
		$manifest['icons'][] = [
			'src'     => HYPWA_Options::get( 'app_icon' ),
			'sizes'   => HYPWA_Options::get( 'app_icon_width', 0 ) . 'x' . HYPWA_Options::get( 'app_icon_height', 0 ),
			'type'    => 'image/png',
			'purpose' => 'any',
		];
	}

	if ( HYPWA_Options::get( 'maskable_icon' ) ) {
		$manifest['icons'][] = [
			'src'     => HYPWA_Options::get( 'maskable_icon' ),
			'sizes'   => HYPWA_Options::get( 'maskable_icon_width', 0 ) . 'x' . HYPWA_Options::get( 'maskable_icon_height', 0 ),
			'type'    => 'image/png',
			'purpose' => 'maskable',
		];
	}

	if ( HYPWA_Options::get( 'monochrome_icon' ) ) {
		$manifest['icons'][] = [
			'src'     => HYPWA_Options::get( 'monochrome_icon' ),
			'sizes'   => HYPWA_Options::get( 'monochrome_icon_width', 0 ) . 'x' . HYPWA_Options::get( 'monochrome_icon_height', 0 ),
			'type'    => 'image/png',
			'purpose' => 'monochrome',
		];
	}

	if ( HYPWA_Options::get( 'cf_legacy_icon_status' ) ) {

		foreach ( [ 72, 96, 128, 144, 152, 192, 384 ] as $size ) {

			if ( HYPWA_Options::get( "cf_legacy_app_icon_{$size}" ) ) {

				$manifest['icons'][] = [
					'src'   => HYPWA_Options::get( "cf_legacy_app_icon_{$size}" ),
					'sizes' => HYPWA_Options::get( "cf_legacy_app_icon_{$size}_width", 0 ) . 'x' . HYPWA_Options::get( "cf_legacy_app_icon_{$size}_height", 0 ),
					'type'  => 'image/png',
				];
			}
		}
	}

	if ( HYPWA_Options::get( 'cf_screenshots_status' ) ) {

		foreach ( [ 'narrow', 'wide' ] as $form_factor ) {

			$screenshots = HYPWA_Options::get( "cf_screenshots_{$form_factor}" );

			if ( empty( $screenshots ) || ! is_array( $screenshots ) ) {
				continue;
			}

			foreach ( $screenshots as $screenshot ) {

				if ( empty( $screenshot['url'] ) ) {
					continue;
				}

				$manifest['screenshots'][] = [
					'src'         => $screenshot['url'],
					'sizes'       => "{$screenshot['width']}x{$screenshot['height']}",
					'type'        => 'image/png',
					'form_factor' => $form_factor,
				];
			}
		}
	}

	return apply_filters( 'hypwa_manifest_template', $manifest );
}
