<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HYPWA_Options' ) ) {

    final class HYPWA_Options {

        const OPTION_KEY = 'hypwa_options';

        
        // Register & Init

        public static function register() {
            if ( false === get_option( self::OPTION_KEY ) ) {
                add_option( self::OPTION_KEY, self::get_defaults() );
            }

            register_setting(
                self::OPTION_KEY,
                self::OPTION_KEY,
                array( __CLASS__, 'sanitize' )
            );
        }

        // Defaults

        public static function get_defaults() {
            return apply_filters( 'hypwa_default_options', array(
                // App Basics
                'app_name'                    => get_bloginfo('name'),
                'app_short_name'              => get_bloginfo('name'),
                'app_description'             => get_bloginfo('description'),
                'app_icon'                    => HYPWA_DIR_URI . 'assets/images/wordpress-512x512.png',
                'app_icon_width'              => 512,
                'app_icon_height'             => 512,
                'maskable_icon'               => '',
                'monochrome_icon'             => '',
                'splash_screen_icon'          => '',
                'splash_screen_maskable_icon' => '',
                'start_page'                  => '',
                '404_page'                    => '',
                'offline_page'                => '',
                'orientation'                 => 'portrait',
                'launch_mode'                 => 'standalone',
                'background_color'            => '#ffffff',
                'theme_color'                 => '#2563eb',
                'cf_caching_status'           => '0',
                'cf_pre_caching_status'       => '0',
                'cf_utm_tracking_status'      => '0',
                'cf_screenshots_status'       => '0',
                'cf_legacy_icon_status'       => '0',
                'cf_exclude_from_caching_status'=> '0',
                'cf_connectivity_notices_status'=> '0',
                'cf_page_cache_strategy'      => 'stale_while_revalidate',  
                'cf_static_assets_cache_strategy'    => 'cache_first',  
                'cf_image_cache_strategy'     => 'cache_first', 
                'cf_utm_source'               => 'hypa-pwa',      
                'cf_utm_medium'               => 'hypa-pwa',      
                'cf_utm_campaign'             => 'hypwa-launch',      
                'cf_utm_term'                 => 'hypwa-term',      
                'cf_utm_content'              => 'homescreen',  
                'cf_pre_cache_post_types'     => array(),
                'cf_pre_cache_manual_urls'    => '',
                'cf_screenshots_narrow'       => array(),
                'cf_screenshots_wide'         => array(),
                'cf_legacy_app_icon_72'       => '', 
                'cf_legacy_app_icon_72_width'   => 0,
                'cf_legacy_app_icon_72_height'  => 0,                     
                'cf_legacy_app_icon_96'         => '',                      
                'cf_legacy_app_icon_96_width'   => 0,                      
                'cf_legacy_app_icon_96_width'   => 0,                      
                'cf_legacy_app_icon_128'        => '',                      
                'cf_legacy_app_icon_128_width'  => 0,                      
                'cf_legacy_app_icon_128_height' => 0,                      
                'cf_legacy_app_icon_144'        => '',                      
                'cf_legacy_app_icon_144_width'  => 0,                      
                'cf_legacy_app_icon_144_height' => 0,                      
                'cf_legacy_app_icon_152'        => '',                      
                'cf_legacy_app_icon_152_width'  => 0,                      
                'cf_legacy_app_icon_152_height' => 0,                      
                'cf_legacy_app_icon_192'        => '',                      
                'cf_legacy_app_icon_192_width'  => 0,                      
                'cf_legacy_app_icon_192_height' => 0,                      
                'cf_legacy_app_icon_384'        => '',                      
                'cf_legacy_app_icon_384_width'  => 0,                      
                'cf_legacy_app_icon_384_height' => 0,                      
                'cf_exclude_caching_post_types' => array(),                      
                'cf_exclude_caching_taxonomies' => array(),                      
                'cf_exclude_caching_posts'      => array(),                      
                'cf_exclude_caching_url_patterns' => '',                      
                'file_serving_method'          => 'dynamic',                      
                'force_update'                 => '1.0',                      
                'preload_app_manifiest'        => '1',                      
                'cache_external'     => '1', 
                'custom_install_trigger'       => '',                      
                'cf_conn_notice_title'         => "You're Offline",                      
                'cf_conn_notice_description'   => "It looks like you are not connected to the internet. Please check your connection and try again.",               
                'cf_conn_notice_bg_color'      => "#2563eb",                      
                'cf_conn_notice_text_color'    => "#ffffff",
                'cf_conn_notice_icon'          => "dashicons-wifi",
                'cf_conn_online_notice_title'         => "Back online",                      
                'cf_conn_online_notice_description'   => "Your internet connection has been restored.",               
                'cf_conn_online_notice_bg_color'      => "#16a34a",                      
                'cf_conn_online_notice_text_color'    => "#ffffff", 
                'cf_conn_online_notice_icon'          => "dashicons-wifi", 

                'comp_one_signal'              => '0',                                     
                'comp_webpushr'                => '0',                     
                'comp_gravitec'                => '0',                     
                'comp_airlift'                 => '0',
                'comp_wp_rocket'               => '0',
                'comp_litespeed'               => '0',
                'comp_autoptimize'             => '0',
                'comp_wpfc'                    => '0',
                'comp_w3tc'                    => '0',
                'comp_wpsc'                    => '0',
                'fix_mixed_content'            => '0',                     
 
                'remove_data_on_uninstall'     => '0',                 

                // iOS / Safari Compatibility
                'apple_touch_icon'             => HYPWA_DIR_URI . 'assets/images/apple-touch-icon.png',
                'apple_status_bar_style'       => 'default',
                'ios_splash_screens_enabled'   => '1',
                'ios_prompt_status'            => '1',
                'ios_prompt_title'             => 'Add to Home Screen',
                'ios_prompt_desc'              => 'Install this app on your device for offline support and quick access.',
                'ios_prompt_step1'             => 'Tap the Share button [share_icon] in the browser toolbar.',
                'ios_prompt_step2'             => 'Scroll down and select Add to Home Screen.',

                // Push Notifications
                'cf_push_status'               => '0',
                'cf_push_api_key'              => '',
                'cf_push_send_on_publish'      => '0',
                'cf_push_website_id'           => '',
                'cf_push_website_uuid'         => '',
                'cf_push_connected'            => '0',

                // Install Button
                'cf_install_button_status'     => '0',
                'cf_ib_text'                   => 'Install App',
                'cf_ib_bg_color'               => '#2563eb',
                'cf_ib_text_color'             => '#ffffff',
                'cf_ib_border_radius'          => '8',
                'cf_ib_padding'                => '12px 24px',
                'cf_gutenberg_block_status'    => '0',
                'cf_elementor_widget_status'   => '0',
            ));
        }

        // Get All Options

        public static function get_options() {
            $defaults = self::get_defaults();
            $options  = get_option( self::OPTION_KEY, $defaults );
            return apply_filters( 'hypwa_get_options', wp_parse_args( $options, $defaults ) );
        }

        public static function get( $key, $default = false ) {
            $options = (array) self::get_options();
            $value   = array_key_exists( $key, $options ) ? $options[ $key ] : $default;

            if ( 'apple_touch_icon' === $key && empty( $value ) ) {
                $value = HYPWA_DIR_URI . 'assets/images/apple-touch-icon.png';
            }

            $value   = apply_filters( 'hypwa_get_option', $value, $key, $default );
            return apply_filters( 'hypwa_get_option_' . $key, $value, $key, $default );
        }

        // Set Single Option

        public static function set( $key, $value = false ) {
            if ( empty( $value ) ) {
                return self::delete( $key );
            }

            $options         = self::get_options();
            $options[ $key ] = apply_filters( 'hypwa_update_option', $value, $key );

            return update_option( self::OPTION_KEY, $options );
        }

        // Delete Single Option

        public static function delete( $key ) {
            $options = get_option( self::OPTION_KEY, array() );

            if ( array_key_exists( $key, $options ) ) {
                unset( $options[ $key ] );
            }

            return update_option( self::OPTION_KEY, $options );
        }

        // Reset All to Defaults

        public static function reset() {
            delete_option( self::OPTION_KEY );
            return add_option( self::OPTION_KEY, self::get_defaults() );
        }

        // Sanitize on Save

        public static function sanitize( $input ) {
            $output  = array();

            // Text fields
            $text_fields = array( 'app_name', 'app_short_name', 'cf_utm_source', 'cf_utm_medium', 'cf_utm_campaign', 'cf_utm_term', 'cf_utm_content', 'force_update', 'cf_conn_notice_title', 'cf_conn_online_notice_title', 'ios_prompt_title', 'ios_prompt_step1', 'ios_prompt_step2', 'cf_push_api_key', 'cf_push_website_id', 'cf_push_website_uuid', 'cf_push_connected', 'cf_ib_text', 'cf_ib_border_radius', 'cf_ib_padding', 'cf_conn_notice_icon', 'cf_conn_online_notice_icon' );
            foreach ( $text_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $output[ $field ] = sanitize_text_field( $input[ $field ] );
                }
            }

            // Textarea (preserves line breaks)
            $text_area_fields = array( 'app_description', 'cf_caching_short_desc', 'cf_utm_tracking_short_desc','cf_aiq_short_desc', 'cf_atarget_short_desc', 'cf_atarget_app_name', 'cf_exclude_caching_url_patterns', 'cf_conn_notice_description', 'custom_install_trigger', 'cf_conn_online_notice_description', 'ios_prompt_desc' );
            foreach ( $text_area_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $output[ $field ] = sanitize_textarea_field( $input[ $field ] );
                }
            }

            // Numbers
            $text_num_fields = array( 'cf_caching_number', 'cf_utm_tracking_number', 'cf_aiq_number', 'cf_atarget_number' );
            foreach ( $text_num_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $output[ $field ] = (float) sanitize_text_field( $input[ $field ] );
                }
            }

            // Colors
            $text_color_fields = array( 'cf_conn_notice_bg_color', 'cf_conn_notice_text_color', 'background_color', 'theme_color', 'cf_conn_online_notice_bg_color', 'cf_conn_online_notice_text_color', 'cf_ib_bg_color', 'cf_ib_text_color' );
            foreach ( $text_color_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $output[ $field ] = sanitize_hex_color( $input[ $field ] );
                }
            }

            $text_toggle_fields = array( 'cf_caching_status', 'cf_pre_caching_status', 'cf_utm_tracking_status', 'cf_screenshots_status', 'cf_legacy_icon_status', 'cf_exclude_from_caching_status', 'preload_app_manifiest', 'cache_external', 'cf_connectivity_notices_status', 'comp_one_signal', 'comp_webpushr', 'comp_gravitec', 'comp_airlift', 'comp_wp_rocket', 'comp_litespeed', 'comp_autoptimize', 'comp_wpfc', 'comp_w3tc', 'comp_wpsc', 'fix_mixed_content', 'remove_data_on_uninstall', 'ios_splash_screens_enabled', 'ios_prompt_status', 'cf_push_status', 'cf_push_send_on_publish', 'cf_install_button_status', 'cf_gutenberg_block_status', 'cf_elementor_widget_status' );
            foreach ( $text_toggle_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $output[ $field ] = $input[ $field ] == '1' ? '1' : '0';
                }
            }

            // URL fields (icons & uploads)
            $url_fields = array(
                'app_icon',
                'maskable_icon',
                'monochrome_icon',
                'splash_screen_icon',
                'splash_screen_maskable_icon',
                'cf_caching_app_icon',
                'cf_utm_tracking_app_icon',
                'cf_legacy_app_icon',
                'cf_atarget_app_icon',
                'cf_legacy_app_icon_72',
                'cf_legacy_app_icon_96',
                'cf_legacy_app_icon_128',
                'cf_legacy_app_icon_144',
                'cf_legacy_app_icon_152',
                'cf_legacy_app_icon_192',
                'cf_legacy_app_icon_384',
                'apple_touch_icon',
            );
            foreach ( $url_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    $clean_url = esc_url_raw( $input[ $field ] );
                    $width     = 0;
                    $height    = 0;

                    if ( ! empty( $clean_url ) ) {
                        // Try WordPress attachment first (no HTTP request)
                        $attachment_id = attachment_url_to_postid( $clean_url );

                        if ( $attachment_id ) {
                            $meta   = wp_get_attachment_metadata( $attachment_id );
                            $width  = isset( $meta['width'] )  ? (int) $meta['width']  : 0;
                            $height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
                        }

                        // Fallback: getimagesize() for external or unattached images
                        if ( ! $width || ! $height ) {
                            // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                            $size   = @getimagesize( $clean_url );
                            $width  = $size ? (int) $size[0] : 0;
                            $height = $size ? (int) $size[1] : 0;
                        }
                    }

                    $output[ $field ]                    = $clean_url;
                    $output[ $field . '_width' ]         = $width;
                    $output[ $field . '_height' ]        = $height;
                }
            }

            // Page ID select fields
            $page_fields = array( 'start_page', '404_page', 'offline_page', 'cf_page_cache_strategy', 'cf_static_assets_cache_strategy', 'cf_image_cache_strategy', 'file_serving_method' );
            foreach ( $page_fields as $field ) {
                if ( isset( $input[ $field ] ) ) {
                    if ( is_string( $input[ $field ] ) ) {
                        $output[ $field ] = sanitize_text_field( wp_unslash( $input[ $field ] ) );
                    }else{
                        $output[ $field ] = absint( $input[ $field ] );    
                    }
                    
                }
            }

            // Whitelisted select fields
            $allowed_orientation = array( 'follow-device-orientation', 'portrait', 'landscape' );
            if ( isset( $input['orientation'] ) && in_array( $input['orientation'], $allowed_orientation, true ) ) {
                $output['orientation'] = $input['orientation'];
            } else {
                $output['orientation'] = 'portrait';
            }

            $allowed_launch_mode = array( 'full-screen', 'standalone', 'minimal-ui', 'browser' );
            if ( isset( $input['launch_mode'] ) && in_array( $input['launch_mode'], $allowed_launch_mode, true ) ) {
                $output['launch_mode'] = $input['launch_mode'];
            } else {
                $output['launch_mode'] = 'standalone';
            }

            $allowed_status_bar = array( 'default', 'black', 'black-translucent' );
            if ( isset( $input['apple_status_bar_style'] ) && in_array( $input['apple_status_bar_style'], $allowed_status_bar, true ) ) {
                $output['apple_status_bar_style'] = $input['apple_status_bar_style'];
            } else {
                $output['apple_status_bar_style'] = 'default';
            }

            $allowed_multiples = array( 'cf_exclude_caching_post_types', 'cf_exclude_caching_taxonomies' );
            foreach ( $allowed_multiples as $multiple ) {
                if ( isset( $input[ $multiple ] ) ) {
                    $values = is_array( $input[ $multiple ] ) ? $input[ $multiple ] : [ $input[ $multiple ] ];
                    $output[ $multiple ] = array_values( array_filter( array_map( 'sanitize_key', $values ) ) );
                }
            }

            $allowed_multiples = array( 'cf_exclude_caching_posts' );
            foreach ( $allowed_multiples as $multiple ) {
                if ( isset( $input[ $multiple ] ) && is_array( $input[ $multiple ] ) ) {
                    $output[ $multiple ] = array_values( array_filter( array_map( 'absint', $input[ $multiple ] ) ) );
                }
            }


            // Sanitize screenshot repeater fields
            $screenshot_keys = array( 'cf_screenshots_narrow', 'cf_screenshots_wide' );

            foreach ( $screenshot_keys as $ss_key ) {
                if ( isset( $input[ $ss_key ] ) && is_array( $input[ $ss_key ] ) ) {

                    $clean_rows = array();

                    foreach ( $input[ $ss_key ] as $row ) {
                        if ( ! is_array( $row ) ) {
                            continue;
                        }

                        $clean_url = isset( $row['url'] ) ? esc_url_raw( trim( $row['url'] ) ) : '';
                        $width     = 0;
                        $height    = 0;

                        if ( ! empty( $clean_url ) ) {
                            
                            $attachment_id = attachment_url_to_postid( $clean_url );

                            if ( $attachment_id ) {
                                $meta   = wp_get_attachment_metadata( $attachment_id );
                                $width  = isset( $meta['width'] )  ? (int) $meta['width']  : 0;
                                $height = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
                            }

                            // Fallback: getimagesize() for external or unattached images
                            if ( ! $width || ! $height ) {
                                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                                $size   = @getimagesize( $clean_url );
                                $width  = $size ? (int) $size[0] : 0;
                                $height = $size ? (int) $size[1] : 0;
                            }

                            $clean_rows[] = array(
                                'url'    => $clean_url,
                                'width'  => $width,
                                'height' => $height,
                            );
                        }
                    }

                    $output[ $ss_key ] = $clean_rows;
                }
            }
            
            // Sanitize pre caching fields
            if ( isset( $input['cf_pre_cache_post_types'] ) && is_array( $input['cf_pre_cache_post_types'] ) ) {
             
                $clean_pt = array();
                $public_post_types = array_keys( get_post_types( [ 'public' => true ] ) );
             
                foreach ( $input['cf_pre_cache_post_types'] as $pt_slug => $pt_data ) {
             
                    // Only allow real public post types
                    if ( ! in_array( $pt_slug, $public_post_types, true ) ) continue;
                    if ( ! is_array( $pt_data ) ) continue;
             
                    $enabled  = ! empty( $pt_data['enabled'] ) && $pt_data['enabled'] === '1' ? '1' : '0';
                    $count    = isset( $pt_data['count'] ) ? absint( $pt_data['count'] ) : 0;
                    $specific = array();
             
                    if ( isset( $pt_data['specific'] ) && is_array( $pt_data['specific'] ) ) {
                        foreach ( $pt_data['specific'] as $post_id ) {
                            $clean_id = absint( $post_id );
                            if ( $clean_id > 0 ) {
                                $specific[] = $clean_id;
                            }
                        }
                    }
             
                    $clean_pt[ $pt_slug ] = array(
                        'enabled'  => $enabled,
                        'count'    => $count,
                        'specific' => $specific,
                    );
                }
             
                $output['cf_pre_cache_post_types'] = $clean_pt;
            }
             
            // Pre Caching: manual URLs (comma-separated)
            if ( isset( $input['cf_pre_cache_manual_urls'] ) ) {
                $raw_urls   = sanitize_textarea_field( $input['cf_pre_cache_manual_urls'] );
                
                $output['cf_pre_cache_manual_urls'] = $raw_urls;
            }
            
            return apply_filters( 'hypwa_sanitize_options', $output, $input );
        }
    }

    add_action( 'admin_init', array( 'HYPWA_Options', 'register' ) );
}