<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle File Serving Method options synchronization and static file generation/deletion.
 */
function hypwa_sync_static_files( $options = null ) {
	// Security check: Ensure user has permission to manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( null === $options ) {
		$options = HYPWA_Options::get_options();
	}

	$serving_method = isset( $options['file_serving_method'] ) ? $options['file_serving_method'] : 'dynamic';

	// We need WP_Filesystem to be initialized
	require_once ABSPATH . 'wp-admin/includes/file.php';
	
	if ( 'static' === $serving_method ) {
		if ( WP_Filesystem() ) {
			global $wp_filesystem;

			$manifest_content = wp_json_encode( hypwa_manifest_template() );
			$sw_content       = hypwa_service_worker_template();

			$manifest_file = ABSPATH . hypwa_manifest_filename();
			$sw_file       = ABSPATH . hypwa_sw_filename();

			$chmod = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;

			$wp_filesystem->put_contents( $manifest_file, $manifest_content, $chmod );
			$wp_filesystem->put_contents( $sw_file, $sw_content, $chmod );
		}
	} else {
		// dynamic - delete physical files from root if they exist
		if ( WP_Filesystem() ) {
			global $wp_filesystem;

			$manifest_file = ABSPATH . hypwa_manifest_filename();
			$sw_file       = ABSPATH . hypwa_sw_filename();

			if ( $wp_filesystem->exists( $manifest_file ) ) {
				$wp_filesystem->delete( $manifest_file );
			}
			if ( $wp_filesystem->exists( $sw_file ) ) {
				$wp_filesystem->delete( $sw_file );
			}
		}
	}

	// Flush rewrite rules to reflect serving method change (dynamic vs static)
	flush_rewrite_rules();
}

/**
 * Hook when options are updated.
 */
function hypwa_on_options_saved( $old_value, $value, $option ) {
	hypwa_sync_static_files( $value );
}
add_action( 'update_option_hypwa_options', 'hypwa_on_options_saved', 10, 3 );

/**
 * Hook when option is added.
 */
function hypwa_on_options_added( $option, $value ) {
	hypwa_sync_static_files( $value );
}
add_action( 'add_option_hypwa_options', 'hypwa_on_options_added', 10, 2 );

/**
 * Hook when option is deleted.
 */
function hypwa_on_options_deleted() {
	// Security check: Ensure user has permission to manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( WP_Filesystem() ) {
		global $wp_filesystem;

		$manifest_file = ABSPATH . hypwa_manifest_filename();
		$sw_file       = ABSPATH . hypwa_sw_filename();

		if ( $wp_filesystem->exists( $manifest_file ) ) {
			$wp_filesystem->delete( $manifest_file );
		}
		if ( $wp_filesystem->exists( $sw_file ) ) {
			$wp_filesystem->delete( $sw_file );
		}
	}
}
add_action( 'delete_option_hypwa_options', 'hypwa_on_options_deleted' );



/**
 * Hook for plugin activation.
 */
function hypwa_activate() {
	hypwa_sync_static_files();
}
register_activation_hook( HYPWA_DIR_NAME_FILE, 'hypwa_activate' );
