<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class HYPWA_Migrate_Settings {

    public static function migrate_super_pwa_plugin_settings() {
        
        $hypwa_settings         =   HYPWA_Options::get_options();
        $superpwa_settings      =   get_option( 'superpwa_settings' );

        if ( ! empty( $superpwa_settings ) && is_array( $superpwa_settings ) ) {

            $migrated_options   =   [];
            if ( isset( $superpwa_settings['app_name'] ) ) {
                $migrated_options['app_name']                       =   sanitize_text_field( $superpwa_settings['app_name'] );   
            }
            if ( isset( $superpwa_settings['app_short_name'] ) ) {
                $migrated_options['app_short_name']                 =   sanitize_text_field( $superpwa_settings['app_short_name'] );   
            }
            if ( isset( $superpwa_settings['description'] ) ) {
                $migrated_options['app_description']                =   sanitize_textarea_field( $superpwa_settings['description'] );   
            }
            if ( isset( $superpwa_settings['icon'] ) ) {
                $migrated_options['app_icon']                       =   sanitize_url( $superpwa_settings['icon'] );   
            }
            if ( isset( $superpwa_settings['app_maskable_icon'] ) ) {
                $migrated_options['maskable_icon']                  =   sanitize_url( $superpwa_settings['app_maskable_icon'] );   
            }
            if ( isset( $superpwa_settings['monochrome_icon'] ) ) {
                $migrated_options['monochrome_icon']                =   sanitize_url( $superpwa_settings['monochrome_icon'] );   
            }
            if ( isset( $superpwa_settings['splash_icon'] ) ) {
                $migrated_options['splash_screen_icon']             =   sanitize_url( $superpwa_settings['splash_icon'] );   
            }
            if ( isset( $superpwa_settings['splash_maskable_icon'] ) ) {
                $migrated_options['splash_screen_maskable_icon']               =   sanitize_url( $superpwa_settings['splash_maskable_icon'] );   
            }

            $narrow_screenshots     =   [];
            $wide_screenshots       =   [];

            if ( isset( $superpwa_settings['screenshots'] ) && isset( $superpwa_settings['form_factor'] ) ) {
                if ( $superpwa_settings['form_factor'] === 'wide' ) {
                    $wide_screenshots[]         =   [ 'url' => sanitize_url( $superpwa_settings['screenshots'] ) ];    
                }else if( $superpwa_settings['form_factor'] === 'narrow' ) {
                    $narrow_screenshots[]       =   [ 'url' => sanitize_url( $superpwa_settings['screenshots'] ) ];    
                }
            }

            if ( ! empty( $superpwa_settings['screenshots_multiple'] ) && is_array( $superpwa_settings['screenshots_multiple'] ) && ! empty( $superpwa_settings['form_factor_multiple'] ) && is_array( $superpwa_settings['form_factor_multiple'] )  ) {

                foreach ( $superpwa_settings['form_factor_multiple'] as $key => $factor ) {

                    $screenshot                 =   isset( $superpwa_settings['screenshots_multiple'][$key] ) ? $superpwa_settings['screenshots_multiple'][$key] : '';
                    if ( ! empty( $factor ) && $factor === 'wide' ) {
                        $wide_screenshots[]     =   [ 'url' => $screenshot ];
                    } else if ( ! empty( $factor ) && $factor === 'narrow' ) {
                        $narrow_screenshots[]   =   [ 'url' => sanitize_url( $superpwa_settings['screenshots'] ) ]; 
                    }
                }

            }

            if ( ! empty( $narrow_screenshots ) ) {
                $migrated_options['cf_screenshots_narrow']              =   $narrow_screenshots;  
            }
            if ( ! empty( $wide_screenshots ) ) {
                $migrated_options['cf_screenshots_wide']                =   $wide_screenshots;  
            }
            if ( isset( $superpwa_settings['start_url'] ) ) {
                $migrated_options['start_page']                         =   absint( $superpwa_settings['start_url'] );   
            }
            if ( isset( $superpwa_settings['offline_page'] ) ) {
                $migrated_options['offline_page']                       =   absint( $superpwa_settings['offline_page'] );   
            }
            if ( isset( $superpwa_settings['orientation'] ) ) {
                if ( $superpwa_settings['orientation'] == 0 ) {
                    $migrated_options['orientation']                    =   'Follow Device Orientation';       
                } else if ( $superpwa_settings['orientation'] == 1 ) {
                    $migrated_options['orientation']                    =   'Portrait';       
                } else if ( $superpwa_settings['orientation'] == 2 ) {
                    $migrated_options['orientation']                    =   'Landscape';       
                } 
            }
            if ( isset( $superpwa_settings['display'] ) ) {
                if ( $superpwa_settings['display'] == 0 ) {
                    $migrated_options['launch_mode']                    =   'Full Screen';       
                } else if ( $superpwa_settings['display'] == 1 ) {
                    $migrated_options['launch_mode']                    =   'Standalone';       
                } else if ( $superpwa_settings['display'] == 2 ) {
                    $migrated_options['launch_mode']                    =   'Minimal UI';       
                }else if ( $superpwa_settings['display'] == 3 ) {
                    $migrated_options['launch_mode']                    =   'Browser';       
                }
            }
            if ( isset( $superpwa_settings['offline_message'] ) ) {
                $migrated_options['cf_connectivity_notices_status']     =   absint( $superpwa_settings['offline_message'] );   
            }
            if ( isset( $superpwa_settings['offline_message_txt'] ) ) {
                $migrated_options['cf_conn_notice_title']               =   sanitize_text_field( $superpwa_settings['offline_message_txt'] );   
            }

            // Add-on Settings
            $active_addons  =   get_option( 'superpwa_active_addons', [] );

            // App Shortcuts
            $is_shortcut_active     =   self::is_superpwa_adon_active( $active_addons, 'app_shortcut' );
            if ( $is_shortcut_active ) {

                $migrated_options['pf_app_shortcuts_status']            =   '1';  

                $options    =   get_option( 'superpwa_app_shortcut_settings', [] );
                if ( ! empty( $options['shortcuts'] ) && is_array( $options['shortcuts'] ) ) {
                    foreach ( $options['shortcuts'] as $key => $shortcuts ) {
                        if ( ! empty( $shortcuts ) && is_array( $shortcuts ) ) {
                            $migrated_options['pf_app_shortcuts'][]     =   [
                                                                                'name'      =>  isset( $shortcuts['name'] ) ? sanitize_text_field( $shortcuts['name'] ) : '',
                                                                                'url'       =>  isset( $shortcuts['url'] ) ? sanitize_url( $shortcuts['url'] ) : '',
                                                                                'icon'      =>  isset( $shortcuts['icons'] ) ? sanitize_url( $shortcuts['icons'] ) : '',
                                                                            ];    
                        }
                    }
                }
                
            }

            // Caching Strategies & Pre caching Strategies
            $is_caching_active     =   self::is_superpwa_adon_active( $active_addons, 'caching_strategies' );
            if ( $is_caching_active ) {

                $migrated_options['cf_caching_status']                   =   '1';  
                $migrated_options['cf_pre_caching_status']               =   '1';  

                $options    =   get_option( 'superpwa_caching_strategies_settings', [] );
                
                if ( ! empty( $options ) && is_array( $options ) ) {
                    
                    $count  =   ! empty( $options['precaching_post_count'] ) ? absint( $options['precaching_post_count'] ) : 0;
                    if ( ! empty( $options['caching_type'] ) ) {
                        $migrated_options['cf_page_cache_strategy']  =  sanitize_text_field( $options['caching_type'] );         
                    }
                    if ( ! empty( $options[ 'precaching_automatic_post' ] ) ) {
                        $migrated_options['cf_pre_cache_post_types']['post']['enabled']     =   '1';
                        $migrated_options['cf_pre_cache_post_types']['post']['count']       =   $count;
                    }
                    if ( ! empty( $options[ 'precaching_automatic_page' ] ) ) {
                        $migrated_options['cf_pre_cache_post_types']['page']['enabled']     =   '1';
                        $migrated_options['cf_pre_cache_post_types']['page']['count']       =   $count;
                    }
                    if ( ! empty( $options[ 'precaching_urls' ] ) ) {
                        $migrated_options['cf_pre_cache_manual_urls']     =   sanitize_textarea_field( $options[ 'precaching_urls' ] );
                    }
    
                }
                
            }

            // Call to action
            $is_cta_active     =   self::is_superpwa_adon_active( $active_addons, 'call_to_action' );
            if ( $is_cta_active ) {

                $migrated_options['pf_custom_install_app_status']                   =   '1';  

                $options    =   get_option( 'superpwa_call_to_action_settings', [] );

                if ( ! empty( $options ) && is_array( $options ) ) {

                    if ( ! empty( $options['add_to_home_msg'] ) ) {
                        $migrated_options['pf_cia_title']           =   sanitize_text_field( $options['add_to_home_msg'] );            
                    }
                    if ( ! empty( $options['add_to_home_pos'] ) ) {
                        $position   =   '';
                        if ( $options['add_to_home_pos'] == 'top' ) {
                            $position   =   'top_fixed';
                        }else if ( $options['add_to_home_pos'] == 'bottom' ) {
                            $position   =   'bottom_fixed';
                        }
                        $migrated_options['pf_cia_position']        =   $position;            
                    }
                    if ( ! empty( $options['a2h_banner_delay_sec_cta'] ) ) {
                        $migrated_options['pf_cia_show_delay']      =   absint( $options['a2h_banner_delay_sec_cta'] );            
                    }
                    if ( ! empty( $options['a2h_sticky_on_tablet'] ) ) {
                        $migrated_options['pf_cia_exclude_tablet']  =   '0';            
                    }
                    if ( ! empty( $options['a2h_sticky_on_android'] ) ) {
                        $migrated_options['pf_cia_exclude_mobile']  =   '0';            
                    }
                    if ( ! empty( $options['a2h_sticky_on_desktop'] ) ) {
                        $migrated_options['pf_cia_exclude_desktop'] =   '0';            
                    }
                    if ( ! empty( $options['bar_bg_color'] ) ) {
                        $migrated_options['pf_cia_bg_color']        =   sanitize_hex_color( $options['bar_bg_color'] );            
                    }
                    if ( ! empty( $options['bar_text_color'] ) ) {
                        $migrated_options['pf_cia_text_color']      =   sanitize_hex_color( $options['bar_text_color'] );           
                    }
                    if ( ! empty( $options['bar_btn_bg_color'] ) ) {
                        $migrated_options['pf_cia_button_color']    =   sanitize_hex_color( $options['bar_btn_bg_color'] );            
                    }
                    if ( ! empty( $options['bar_btn_text_color'] ) ) {
                        $migrated_options['pf_cia_button_text_color']   =   sanitize_hex_color( $options['bar_btn_text_color'] );            
                    }
                    if ( ! empty( $options['add_to_home_btn_text'] ) ) {
                        $migrated_options['pf_cia_cta_text']        =   sanitize_text_field( $options['add_to_home_btn_text'] );            
                    }

                }
            }

            // Bottom Navigation
            $is_nav_active     =   self::is_superpwa_adon_active( $active_addons, 'navigation_bar_for_superpwa' );
            if ( $is_nav_active ) {

                $migrated_options['pf_bn_status']                   =   '1';  

                $options    =   get_option( 'superpwa_navigation_bar_settings', [] );

                if ( ! empty( $options ) && is_array( $options ) ) {

                    if ( ! empty( $options['text_font_size'] ) ) {
                        $migrated_options['pf_bn_font_size']    =   absint( $options['text_font_size'] );            
                    }
                    if ( ! empty( $options['background_color'] ) ) {
                        $migrated_options['pf_bn_bg_color']     =   sanitize_hex_color( $options['background_color'] );            
                    }
                    if ( ! empty( $options['text_font_color'] ) ) {
                        $migrated_options['pf_bn_menu_text_color']   =   sanitize_hex_color( $options['text_font_color'] );            
                    }
                    if ( ! empty( $options['selected_text_font_color'] ) ) {
                        $migrated_options['pf_bn_active_menu_text_color']   =   sanitize_hex_color( $options['selected_text_font_color'] );            
                    }
                    if ( ! empty( $options['icon_color'] ) ) {
                        $migrated_options['pf_bn_menu_icon_color']   =   sanitize_hex_color( $options['icon_color'] );            
                    }
                    if ( ! empty( $options['icon_size'] ) ) {
                        $migrated_options['pf_bn_icon_size']         =   absint( $options['icon_size'] );            
                    } 
                    if ( ! empty( $options['excluded_pages'] ) && is_array( $options['excluded_pages'] ) ) {
                        $migrated_options['pf_bn_exclude_posts']     =   array_map( 'absint', $options['excluded_pages'] );            
                    }                   

                }
            }

            // Polylang
            $is_polylang_active     =   self::is_superpwa_adon_active( $active_addons, 'polylang_for_superpwa' );
            if ( $is_polylang_active ) {
                $migrated_options['pf_multilingual_status']                     =   '1';
                $migrated_options['pf_multilingual_polylang']                   =   '1';
            }

            // Translatepress
            $is_tp_active           =   self::is_superpwa_adon_active( $active_addons, 'translatepress_for_superpwa' );
            if ( $is_tp_active ) {
                $migrated_options['pf_multilingual_status']                     =   '1';
                $migrated_options['pf_multilingual_tp']                         =   '1';
            }

            // Translatepress
            $is_wpml_active         =   self::is_superpwa_adon_active( $active_addons, 'wpml_for_superpwa' );
            if ( $is_wpml_active ) {
                $migrated_options['pf_multilingual_status']                     =   '1';
                $migrated_options['pf_multilingual_wpml']                       =   '1';
            }

            // preloader
            $is_preloader_active     =   self::is_superpwa_adon_active( $active_addons, 'pre_loader' );
            if ( $is_preloader_active ) {

                $migrated_options['pf_initial_loader_status']                   =   '1';  

                $options    =   get_option( 'superpwa_pre_loader_settings', [] );

                if ( ! empty( $options ) && is_array( $options ) ) {

                    if ( ! empty( $options['loading_icon_color'] ) ) {
                        $migrated_options['pf_il_color']   =   sanitize_hex_color( $options['loading_icon_color'] );            
                    }
                    if ( ! empty( $options['loading_icon_bg_color'] ) ) {
                        $migrated_options['pf_il_bg_color']   =   sanitize_hex_color( $options['loading_icon_bg_color'] );            
                    }

                }
            }

            // UTM Tracking
            $is_utm_active     =   self::is_superpwa_adon_active( $active_addons, 'utm_tracking' );
            if ( $is_utm_active ) {

                $migrated_options['cf_utm_tracking_status']                   =   '1';  

                $options    =   get_option( 'superpwa_utm_tracking_settings', [] );

                if ( ! empty( $options ) && is_array( $options ) ) {

                    if ( ! empty( $options['utm_source'] ) ) {
                        $migrated_options['cf_utm_source']   =   sanitize_text_field( $options['utm_source'] );            
                    }
                    if ( ! empty( $options['utm_medium'] ) ) {
                        $migrated_options['cf_utm_medium']   =   sanitize_text_field( $options['utm_medium'] );            
                    }
                    if ( ! empty( $options['utm_campaign'] ) ) {
                        $migrated_options['cf_utm_campaign'] =   sanitize_text_field( $options['utm_campaign'] );            
                    }
                    if ( ! empty( $options['utm_term'] ) ) {
                        $migrated_options['cf_utm_term']     =   sanitize_text_field( $options['utm_term'] );            
                    }
                    if ( ! empty( $options['utm_content'] ) ) {
                        $migrated_options['cf_utm_content']  =   sanitize_text_field( $options['utm_content'] );            
                    }

                }
            }

            // Update the migration
            if ( ! empty( $migrated_options ) && is_array( $migrated_options ) ) {
                
                $updated  = array_merge( $hypwa_settings, $migrated_options );
                update_option( HYPWA_Options::OPTION_KEY, $updated );

            }
        }

    }

    /**
     * Check if superpwa ad-on is active
     * */
    public static function is_superpwa_adon_active( $active_addons, $superpwa_key  ) {

        $flag   =   false;
        
        if ( ! empty( $active_addons ) && is_array( $active_addons ) && in_array( $superpwa_key, $active_addons ) ) {
            $flag   =   true; 
        }

        return $flag;

    }

    public static function migrate_pwa_plugin_settings() {
        
        $hypwa_settings         =   HYPWA_Options::get_options();
        $pwa_settings           =   get_option( 'pwaforwp_settings' );

        if ( ! empty( $pwa_settings ) && is_array( $pwa_settings ) ) {

            $migrated_options   =   [];
            if ( isset( $pwa_settings['app_blog_name'] ) ) {
                $migrated_options['app_name']                               =   sanitize_text_field( $pwa_settings['app_blog_name'] );   
            }
            if ( isset( $pwa_settings['app_blog_short_name'] ) ) {
                $migrated_options['app_short_name']                         =   sanitize_text_field( $pwa_settings['app_blog_short_name'] );   
            }
            if ( isset( $pwa_settings['description'] ) ) {
                $migrated_options['app_description']                        =   sanitize_textarea_field( $pwa_settings['description'] );   
            }
            if ( isset( $pwa_settings['icon'] ) ) {
                $migrated_options['app_icon']                               =   sanitize_url( $pwa_settings['icon'] );   
            }
            if ( isset( $pwa_settings['app_maskable_icon'] ) ) {
                $migrated_options['maskable_icon']                          =   sanitize_url( $pwa_settings['app_maskable_icon'] );   
            }
            if ( isset( $pwa_settings['monochrome'] ) ) {
                $migrated_options['monochrome_icon']                        =   sanitize_url( $pwa_settings['monochrome'] );   
            }
            if ( isset( $pwa_settings['splash_icon'] ) ) {
                $migrated_options['splash_screen_icon']                     =   sanitize_url( $pwa_settings['splash_icon'] );   
            }
            if ( isset( $pwa_settings['splash_maskable_icon'] ) ) {
                $migrated_options['splash_screen_maskable_icon']            =   sanitize_url( $pwa_settings['splash_maskable_icon'] );   
            }
            
            $narrow_screenshots     =   [];
            $wide_screenshots       =   [];

            if ( isset( $pwa_settings['screenshots'] ) && isset( $pwa_settings['form_factor'] ) ) {
                if ( $pwa_settings['form_factor'] === 'wide' ) {
                    $wide_screenshots[]         =   [ 'url' => sanitize_url( $pwa_settings['screenshots'] ) ];    
                }else if( $pwa_settings['form_factor'] === 'narrow' ) {
                    $narrow_screenshots[]       =   [ 'url' => sanitize_url( $pwa_settings['screenshots'] ) ];    
                }
            }

            if ( ! empty( $pwa_settings['screenshots_multiple'] ) && is_array( $pwa_settings['screenshots_multiple'] ) && ! empty( $pwa_settings['form_factor_multiple'] ) && is_array( $pwa_settings['form_factor_multiple'] )  ) {

                foreach ( $pwa_settings['form_factor_multiple'] as $key => $factor ) {

                    $screenshot                 =   isset( $pwa_settings['screenshots_multiple'][$key] ) ? $pwa_settings['screenshots_multiple'][$key] : '';
                    if ( ! empty( $factor ) && $factor === 'wide' ) {
                        $wide_screenshots[]     =   [ 'url' => $screenshot ];
                    } else if ( ! empty( $factor ) && $factor === 'narrow' ) {
                        $narrow_screenshots[]   =   [ 'url' => sanitize_url( $pwa_settings['screenshots'] ) ]; 
                    }else{
                        $wide_screenshots[]     =   [ 'url' => $screenshot ];
                    }
                }

            }

            if ( ! empty( $narrow_screenshots ) ) {
                $migrated_options['cf_screenshots_narrow']              =   $narrow_screenshots;  
            }
            if ( ! empty( $wide_screenshots ) ) {
                $migrated_options['cf_screenshots_wide']                =   $wide_screenshots;  
            }
            if ( isset( $pwa_settings['start_page'] ) ) {
                $migrated_options['start_page']                         =   absint( $pwa_settings['start_page'] );   
            }
            if ( isset( $pwa_settings['offline_page'] ) ) {
                $migrated_options['offline_page']                       =   absint( $pwa_settings['offline_page'] );   
            }
            if ( isset( $pwa_settings['404_page'] ) ) {
                $migrated_options['404_page']                           =   absint( $pwa_settings['404_page'] );   
            }
            if ( isset( $pwa_settings['orientation'] ) ) {
                if ( empty( $pwa_settings['orientation'] ) ) {
                    $migrated_options['orientation']                    =   'Follow Device Orientation';       
                } else if ( $pwa_settings['orientation'] == 'portrait' ) {
                    $migrated_options['orientation']                    =   'Portrait';       
                } else if ( $pwa_settings['orientation'] == 'landscape' ) {
                    $migrated_options['orientation']                    =   'Landscape';       
                } 
            }
            if ( isset( $pwa_settings['display'] ) ) {
                if ( $pwa_settings['display'] == 'fullscreen' ) {
                    $migrated_options['launch_mode']                    =   'Full Screen';       
                } else if ( $pwa_settings['display'] == 'standalone' ) {
                    $migrated_options['launch_mode']                    =   'Standalone';       
                } else if ( $pwa_settings['display'] == 'minimal-ui' ) {
                    $migrated_options['launch_mode']                    =   'Minimal UI';       
                }else if ( $pwa_settings['display'] == 'browser' ) {
                    $migrated_options['launch_mode']                    =   'Browser';       
                }
            }
            if ( ! empty( $pwa_settings['theme_color'] ) ) {
                $migrated_options['pf_il_color']   =   sanitize_hex_color( $pwa_settings['theme_color'] );            
            }
            if ( ! empty( $pwa_settings['background_color'] ) ) {
                $migrated_options['pf_il_bg_color']   =   sanitize_hex_color( $pwa_settings['background_color'] );            
            }

            if ( isset( $pwa_settings['precaching_automatic'] ) ) {
                $migrated_options['cf_caching_status']                   =   (string) $pwa_settings['precaching_automatic'];  
                $migrated_options['cf_pre_caching_status']               =   (string) $pwa_settings['precaching_automatic'];  
                $count  =   ! empty( $pwa_settings['precaching_post_count'] ) ? absint( $pwa_settings['precaching_post_count'] ) : 0;
                if ( ! empty( $pwa_settings[ 'precaching_automatic_post' ] ) ) {
                    $migrated_options['cf_pre_cache_post_types']['post']['enabled']     =   '1';
                    $migrated_options['cf_pre_cache_post_types']['post']['count']       =   $count;
                }
                if ( ! empty( $pwa_settings[ 'precaching_automatic_page' ] ) ) {
                    $migrated_options['cf_pre_cache_post_types']['page']['enabled']     =   '1';
                    $migrated_options['cf_pre_cache_post_types']['page']['count']       =   $count;
                }
                if ( ! empty( $pwa_settings[ 'precaching_urls' ] ) ) {
                    $migrated_options['cf_pre_cache_manual_urls']     =   sanitize_textarea_field( $pwa_settings[ 'precaching_urls' ] );
                }
            }
            if ( isset( $pwa_settings['custom_add_to_home_setting'] ) ) {
                if ( ! empty( $pwa_settings['custom_banner_title'] ) ) {
                        $migrated_options['pf_cia_title']           =   sanitize_text_field( $pwa_settings['custom_banner_title'] );            
                    }
                    if ( ! empty( $pwa_settings['loading_icon_display_mobile'] ) ) {
                        $migrated_options['pf_cia_exclude_mobile']  =   '0';            
                    }
                    if ( ! empty( $pwa_settings['loading_icon_display_desktop'] ) ) {
                        $migrated_options['pf_cia_exclude_desktop'] =   '0';            
                    }
                    if ( ! empty( $pwa_settings['custom_banner_background_color'] ) ) {
                        $migrated_options['pf_cia_bg_color']        =   hypwa_rgb_string_to_hex( $pwa_settings['custom_banner_background_color'] );            
                    }
                    if ( ! empty( $pwa_settings['custom_banner_title_color'] ) ) {
                        $migrated_options['pf_cia_text_color']      =  hypwa_rgb_string_to_hex( $pwa_settings['custom_banner_title_color'] );           
                    }
                    if ( ! empty( $pwa_settings['custom_banner_btn_color'] ) ) {
                        $migrated_options['pf_cia_button_color']    =   hypwa_rgb_string_to_hex( $pwa_settings['custom_banner_btn_color'] );            
                    }
                    if ( ! empty( $pwa_settings['custom_banner_btn_text_color'] ) ) {
                        $migrated_options['pf_cia_button_text_color']   =   hypwa_rgb_string_to_hex( $pwa_settings['custom_banner_btn_text_color'] );            
                    }
                    if ( ! empty( $pwa_settings['custom_banner_button_text'] ) ) {
                        $migrated_options['pf_cia_cta_text']        =   $pwa_settings['custom_banner_button_text'];            
                    }
            }

            // UTM Tracking
            if ( isset( $pwa_settings['utm_setting'] ) ) {
                $migrated_options['cf_utm_tracking_status']                   =   (string) $pwa_settings['utm_setting']; 

                if ( ! empty( $pwa_settings['utm_details']['utm_source'] ) ) {
                        $migrated_options['cf_utm_source']   =   sanitize_text_field( $pwa_settings['utm_details']['utm_source'] );            
                }
                if ( ! empty( $pwa_settings['utm_details']['utm_medium'] ) ) {
                    $migrated_options['cf_utm_medium']   =   sanitize_text_field( $pwa_settings['utm_details']['utm_medium'] );            
                }
                if ( ! empty( $pwa_settings['utm_details']['utm_campaign'] ) ) {
                    $migrated_options['cf_utm_campaign'] =   sanitize_text_field( $pwa_settings['utm_details']['utm_campaign'] );            
                }
                if ( ! empty( $pwa_settings['utm_details']['utm_term'] ) ) {
                    $migrated_options['cf_utm_term']     =   sanitize_text_field( $pwa_settings['utm_details']['utm_term'] );            
                }
                if ( ! empty( $pwa_settings['utm_details']['utm_content'] ) ) {
                    $migrated_options['cf_utm_content']  =   sanitize_text_field( $pwa_settings['utm_details']['utm_content'] );            
                }
            }

            // Loader
            if ( isset( $pwa_settings['loading_icon'] ) ) {
                $migrated_options['pf_initial_loader_status']                   =   (string) $pwa_settings['loading_icon']; 

                if ( ! empty( $pwa_settings['loading_icon_color'] ) ) {
                    $migrated_options['pf_il_color']   =   sanitize_hex_color( $pwa_settings['loading_icon_color'] );            
                }
                if ( ! empty( $pwa_settings['loading_icon_bg_color'] ) ) {
                    $migrated_options['pf_il_bg_color']   =   sanitize_hex_color( $pwa_settings['loading_icon_bg_color'] );            
                }
            }

            // Call to Action
            if ( isset( $pwa_settings['add_to_home_sticky'] ) ) {
                $migrated_options['pf_initial_loader_status']                   =   (string) $pwa_settings['add_to_home_sticky']; 

                if ( ! empty( $pwa_settings['add_to_home_msg'] ) ) {
                        $migrated_options['pf_cia_title']           =   sanitize_text_field( $pwa_settings['add_to_home_msg'] );            
                }
                if ( ! empty( $options['add_to_home_pos'] ) ) {
                    $position   =   '';
                    if ( $options['add_to_home_pos'] == 'top' ) {
                        $position   =   'top_fixed';
                    }else if ( $options['add_to_home_pos'] == 'bottom' ) {
                        $position   =   'bottom_fixed';
                    }
                    $migrated_options['pf_cia_position']        =   $position;            
                }
                if ( ! empty( $options['a2h_banner_delay_sec_cta'] ) ) {
                    $migrated_options['pf_cia_show_delay']      =   absint( $options['a2h_banner_delay_sec_cta'] );            
                }
                if ( ! empty( $options['bar_bg_color'] ) ) {
                    $migrated_options['pf_cia_bg_color']        =   sanitize_hex_color( $options['bar_bg_color'] );            
                }
                if ( ! empty( $options['bar_text_color'] ) ) {
                    $migrated_options['pf_cia_text_color']      =   sanitize_hex_color( $options['bar_text_color'] );           
                }
                if ( ! empty( $options['bar_btn_bg_color'] ) ) {
                    $migrated_options['pf_cia_button_color']    =   sanitize_hex_color( $options['bar_btn_bg_color'] );            
                }
                if ( ! empty( $options['bar_btn_text_color'] ) ) {
                    $migrated_options['pf_cia_button_text_color']   =   sanitize_hex_color( $options['bar_btn_text_color'] );            
                }
                if ( ! empty( $options['add_to_home_btn_text'] ) ) {
                    $migrated_options['pf_cia_cta_text']        =   sanitize_text_field( $options['add_to_home_btn_text'] );            
                }
            }

            // Navigation
            if ( isset( $pwa_settings['navigation_bar'] ) ) {

                $migrated_options['pf_bn_status']                   =   (string) $pwa_settings['navigation_bar'];  

                if ( ! empty( $pwa_settings['navigation'] ) && is_array( $pwa_settings['navigation'] ) ) {

                    if ( ! empty( $pwa_settings['navigation']['text_font_size'] ) ) {
                        $migrated_options['pf_bn_font_size']    =   absint( $pwa_settings['navigation']['text_font_size'] );            
                    }
                    if ( ! empty( $pwa_settings['navigation']['text_background_color'] ) ) {
                        $migrated_options['pf_bn_bg_color']     =   hypwa_rgb_string_to_hex( $pwa_settings['navigation']['text_background_color'] );            
                    }
                    if ( ! empty( $pwa_settings['navigation']['text_font_color'] ) ) {
                        $migrated_options['pf_bn_menu_text_color']   =   hypwa_rgb_string_to_hex( $pwa_settings['navigation']['text_font_color'] );            
                    }
                    if ( ! empty( $pwa_settings['navigation']['selected_text_font_color'] ) ) {
                        $migrated_options['pf_bn_active_menu_text_color']   =   hypwa_rgb_string_to_hex( $pwa_settings['navigation']['selected_text_font_color'] );            
                    }
                    if ( ! empty( $pwa_settings['navigation']['selected_menu_background_color'] ) ) {
                        $migrated_options['pf_bn_active_menu_icon_color']   =   hypwa_rgb_string_to_hex( $pwa_settings['navigation']['selected_menu_background_color'] );            
                    } 
                    if ( ! empty( $pwa_settings['navigation']['excluded_pages'] ) && is_string( $pwa_settings['navigation']['excluded_pages'] ) ) {
                        $explode_pages  =   explode( ',', $pwa_settings['navigation']['excluded_pages'] );
                        if ( ! empty( $explode_pages ) ) {
                            $migrated_options['pf_bn_exclude_posts']     =   array_map( 'absint', $explode_pages );            
                        }
                    }                   

                }
            }
            
            if ( ! empty( $migrated_options ) && is_array( $migrated_options ) ) {
    
                $updated  = array_merge( $hypwa_settings, $migrated_options );
                
                update_option( HYPWA_Options::OPTION_KEY, $updated );

            }
        }


    }

}