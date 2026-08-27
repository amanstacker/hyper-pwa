<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serves a dynamic PNG splash screen for iOS devices with the correct background color and centered logo.
 */
function hypwa_serve_ios_splash() {
	$width  = isset( $_GET['w'] ) ? absint( $_GET['w'] ) : 640;
	$height = isset( $_GET['h'] ) ? absint( $_GET['h'] ) : 1136;

	// Clamp dimensions to a safe range to prevent resource exhaustion
	$width  = max( 100, min( 3000, $width ) );
	$height = max( 100, min( 3000, $height ) );

	$bg_color_hex = HYPWA_Options::get( 'background_color', '#ffffff' );
	$logo_url     = HYPWA_Options::get( 'splash_screen_icon' );
	if ( empty( $logo_url ) ) {
		$logo_url = HYPWA_Options::get( 'app_icon' );
	}

	// Fallback if GD is missing
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		header( 'Content-Type: image/png' );
		// Serve a 1x1 transparent PNG
		echo base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );
		exit;
	}

	// Parse background color hex to RGB
	$bg_color_hex = ltrim( $bg_color_hex, '#' );
	if ( 3 === strlen( $bg_color_hex ) ) {
		$r = hexdec( substr( $bg_color_hex, 0, 1 ) . substr( $bg_color_hex, 0, 1 ) );
		$g = hexdec( substr( $bg_color_hex, 1, 1 ) . substr( $bg_color_hex, 1, 1 ) );
		$b = hexdec( substr( $bg_color_hex, 2, 1 ) . substr( $bg_color_hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $bg_color_hex, 0, 2 ) );
		$g = hexdec( substr( $bg_color_hex, 2, 2 ) );
		$b = hexdec( substr( $bg_color_hex, 4, 2 ) );
	}

	$im = imagecreatetruecolor( $width, $height );
	
	// Support alpha blending for the canvas operations
	imagealphablending( $im, true );
	
	// Fill with solid background color
	$bg_color = imagecolorallocate( $im, $r, $g, $b );
	imagefill( $im, 0, 0, $bg_color );

	// Attempt to load and copy the logo
	if ( ! empty( $logo_url ) ) {
		$logo_path = $logo_url;
		$upload_dir = wp_get_upload_dir();
		
		// If the file is local, replace the base URL with the base path to avoid a slow HTTP loopback connection
		if ( strpos( $logo_url, $upload_dir['baseurl'] ) === 0 ) {
			$logo_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $logo_url );
		}

		if ( ! file_exists( $logo_path ) ) {
			$logo_path = $logo_url;
		}

		$logo_image = null;
		$image_info = @getimagesize( $logo_path );

		if ( $image_info ) {
			$mime = $image_info['mime'];
			switch ( $mime ) {
				case 'image/jpeg':
				case 'image/jpg':
					$logo_image = @imagecreatefromjpeg( $logo_path );
					break;
				case 'image/png':
					$logo_image = @imagecreatefrompng( $logo_path );
					if ( $logo_image ) {
						imagealphablending( $logo_image, true );
						imagesavealpha( $logo_image, true );
					}
					break;
				case 'image/gif':
					$logo_image = @imagecreatefromgif( $logo_path );
					break;
				case 'image/webp':
					if ( function_exists( 'imagecreatefromwebp' ) ) {
						$logo_image = @imagecreatefromwebp( $logo_path );
					}
					break;
			}
		}

		if ( $logo_image ) {
			$logo_w = imagesx( $logo_image );
			$logo_h = imagesy( $logo_image );

			// Maximum bounds for the logo: 30% of viewport width or height
			$max_logo_w = $width * 0.30;
			$max_logo_h = $height * 0.30;

			$scale = min( $max_logo_w / $logo_w, $max_logo_h / $logo_h );
			if ( $scale > 1.0 ) {
				$scale = 1.0; // Don't upscale
			}

			$dest_w = (int) round( $logo_w * $scale );
			$dest_h = (int) round( $logo_h * $scale );

			$dest_x = (int) round( ( $width - $dest_w ) / 2 );
			$dest_y = (int) round( ( $height - $dest_h ) / 2 );

			imagecopyresampled( $im, $logo_image, $dest_x, $dest_y, 0, 0, $dest_w, $dest_h, $logo_w, $logo_h );
			imagedestroy( $logo_image );
		}
	}

	header( 'Content-Type: image/png' );
	header( 'Cache-Control: public, max-age=31536000' );
	
	imagepng( $im );
	imagedestroy( $im );
}
