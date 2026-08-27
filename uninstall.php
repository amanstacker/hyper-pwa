<?php
/**
 * Uninstall Hyper PWA
 *
 * Fired when the plugin is deleted via the WordPress admin.
 * Cleans up all plugin data from the database only when the
 * "Remove data on uninstall" setting is enabled.
 *
 * @package HyperPWA
 */

// Bail if not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// NOTE: Plugin classes are NOT available here — read the raw option directly.
$hypwa_options = get_option( 'hypwa_options', array() );
$hypwa_remove_data   = isset( $hypwa_options['remove_data_on_uninstall'] )
                    ? (string) $hypwa_options['remove_data_on_uninstall']
                    : '0';

if ( '1' !== $hypwa_remove_data ) {
    return;
}

// Load helper to get the sw and manifest filenames
if ( file_exists( dirname( __FILE__ ) . '/includes/customer/helper.php' ) ) {
    require_once dirname( __FILE__ ) . '/includes/customer/helper.php';
}

/**
 * Delete all plugin options and static files.
 */
if ( is_multisite() ) {
    $hypwa_sites = get_sites( array( 'number' => 0 ) );

    foreach ( $hypwa_sites as $hypwa_site ) {
        switch_to_blog( $hypwa_site->blog_id );
        delete_option( 'hypwa_options' );

        // Clean up static files if they exist
        if ( function_exists( 'hypwa_sw_filename' ) ) {
            $sw_file = ABSPATH . hypwa_sw_filename();
            if ( file_exists( $sw_file ) ) {
                @unlink( $sw_file );
            }
        }
        if ( function_exists( 'hypwa_manifest_filename' ) ) {
            $manifest_file = ABSPATH . hypwa_manifest_filename();
            if ( file_exists( $manifest_file ) ) {
                @unlink( $manifest_file );
            }
        }

        restore_current_blog();
    }
} else {
    delete_option( 'hypwa_options' );

    // Clean up static files if they exist
    if ( function_exists( 'hypwa_sw_filename' ) ) {
        $sw_file = ABSPATH . hypwa_sw_filename();
        if ( file_exists( $sw_file ) ) {
            @unlink( $sw_file );
        }
    }
    if ( function_exists( 'hypwa_manifest_filename' ) ) {
        $manifest_file = ABSPATH . hypwa_manifest_filename();
        if ( file_exists( $manifest_file ) ) {
            @unlink( $manifest_file );
        }
    }
}