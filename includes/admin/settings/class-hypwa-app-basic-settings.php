<?php

if (!defined('ABSPATH')) exit;

class HYPWA_App_Basic_Settings {

    public static function render_all_fields() {

        $latest_pages = [];
        $get_pages = get_posts([
            'post_type'      => 'page',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ( ! empty( $get_pages ) && is_array( $get_pages ) ) {
            foreach ( $get_pages as $page_obj ) {
                $latest_pages[ $page_obj->ID ] = $page_obj->post_title;
            }
        }

        $saved_start   = HYPWA_Options::get('start_page');
        $saved_404     = HYPWA_Options::get('404_page');
        $saved_offline = HYPWA_Options::get('offline_page');

        // Helper closure logic to ensure the saved option always exists in the rendered HTML array
        $ensure_saved_exists = function( $saved_id, $current_list ) {
            if ( ! empty( $saved_id ) && ! isset( $current_list[ $saved_id ] ) ) {
                $post = get_post( $saved_id );
                if ( $post ) {
                    $post_type_obj = get_post_type_object( $post->post_type );
                    $label         = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst( $post->post_type );
                    
                    // Append the missing saved post to the options array so it persists on reload
                    $current_list[ $saved_id ] = $post->post_title . ' (' . $label . ')';
                }
            }
            return $current_list;
        };

        // Usage — single consistent call for every field type
        HYPWA_Settings::render('text', [
            'id'            => 'hypwa_app_name_text_field',
            'name'          => 'hypwa_options[app_name]',
            'value'         => HYPWA_Options::get('app_name', get_bloginfo('name')),
            'placeholder'   => '',
            'label'         => esc_html__('Name', 'hyper-pwa'),
            'desc'          => esc_html__('The main title of your app displayed on the user\'s home screen and splash screen.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('text', [
            'id'            => 'hypwa_app_short_name_text_field',
            'name'          => 'hypwa_options[app_short_name]',
            'value'         => HYPWA_Options::get('app_short_name', get_bloginfo('name')),
            'placeholder'   => '',
            'label'         => esc_html__('Short Name', 'hyper-pwa'),
            'desc'          => esc_html__('A shorter version of your app name used when space is limited. Recommended: around 12 characters or fewer.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('textarea', [
            'id'            => 'hypwa_app_description_textarea_field',
            'name'          => 'hypwa_options[app_description]',
            'value'         => HYPWA_Options::get('app_description', get_bloginfo('description')),
            'placeholder'   => '',
            'label'         => esc_html__('Description', 'hyper-pwa'),
            'desc'          => esc_html__('Provide a brief description of your app. It may be displayed on supported devices and platforms to help users understand its purpose.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('upload', [
            'id'            => 'hypwa_app_icon_upload',
            'name'          => 'hypwa_options[app_icon]',
            'value'         => HYPWA_Options::get('app_icon'),
            'label'         => esc_html__('Icon', 'hyper-pwa'),
            'desc'          => esc_html__('The primary launcher icon for your app. Recommended: Square PNG, 512×512 pixels.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('upload', [
            'id'            => 'hypwa_fmaskable_icon_upload',
            'name'          => 'hypwa_options[maskable_icon]',
            'value'         => HYPWA_Options::get('maskable_icon'),
            'label'         => esc_html__('Maskable Icon', 'hyper-pwa'),
            'desc'          => esc_html__('An adaptive icon with a safe zone that allows devices to crop it without cutting off key artwork. Recommended: Square PNG, 512×512 pixels.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('upload', [
            'id'            => 'hypwa_monochrome_icon_upload',
            'name'          => 'hypwa_options[monochrome_icon]',
            'value'         => HYPWA_Options::get('monochrome_icon'),
            'label'         => esc_html__('Monochrome Icon', 'hyper-pwa'),
            'desc'          => esc_html__('A single-color transparent icon used by OS environments for taskbars, notifications, and badges, dynamically tinting to match system themes. Recommended: Square PNG, 512×512 pixels.', 'hyper-pwa'),
        ]);        

        HYPWA_Settings::render('upload', [
            'id'            => 'hypwa_splash_screen_icon_upload',
            'name'          => 'hypwa_options[splash_screen_icon]',
            'value'         => HYPWA_Options::get('splash_screen_icon'),
            'label'         => esc_html__('Splash Screen Icon', 'hyper-pwa'),
            'desc'          => esc_html__('The icon shown in the middle of the splash screen. Recommended: PNG with transparent background, 512×512 pixels.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('select', [
            'class'   => 'hypwa-select2 hypwa-ajax-page-search',
            'id'      => 'hypwa_start_page_select_field',
            'name'    => 'hypwa_options[start_page]',
            'value'   => $saved_start,
            'label'   => esc_html__('Start Page', 'hyper-pwa'),
            'desc'    => esc_html__('The specific landing page that opens immediately when your app is launched.', 'hyper-pwa'),
            'options' => ['' => esc_html__('--Select Start Page--', 'hyper-pwa')] + $ensure_saved_exists($saved_start, $latest_pages),
        ]);

        HYPWA_Settings::render('select', [
            'class'   => 'hypwa-select2 hypwa-ajax-page-search',
            'id'      => 'hypwa_404_page_select_field',
            'name'    => 'hypwa_options[404_page]',
            'value'   => $saved_404,
            'label'   => esc_html__('404 Page', 'hyper-pwa'),
            'desc'    => esc_html__('The fallback error page displayed inside the app if a broken link is encountered.', 'hyper-pwa'),
            'options' => ['' => esc_html__('--Select 404 Page--', 'hyper-pwa')] + $ensure_saved_exists($saved_404, $latest_pages),
        ]);

        HYPWA_Settings::render('select', [
            'class'   => 'hypwa-select2 hypwa-ajax-page-search',
            'id'      => 'hypwa_offline_page_select_field',
            'name'    => 'hypwa_options[offline_page]',
            'value'   => $saved_offline,
            'label'   => esc_html__('Offline Page', 'hyper-pwa'),
            'desc'    => esc_html__('The offline fallback page served when a user accesses the app without internet.', 'hyper-pwa'),
            'options' => ['' => esc_html__('--Select Offline Page--', 'hyper-pwa')] + $ensure_saved_exists($saved_offline, $latest_pages),
        ]);

        HYPWA_Settings::render('select', [
            'class'         => 'hypwa-select2',
            'id'            => 'hypwa_orientation_select_field',
            'name'          => 'hypwa_options[orientation]',
            'value'         => HYPWA_Options::get('orientation', 'portrait'),
            'label'         => esc_html__('Orientation', 'hyper-pwa'),
            'desc'          => esc_html__('Locks the screen display view orientation, or lets it rotate dynamically.', 'hyper-pwa'),
            'options'       => [
                'follow-device-orientation' => esc_html__('Follow Device Orientation', 'hyper-pwa'),
                'portrait'                  => esc_html__('Portrait', 'hyper-pwa'),
                'landscape'                 => esc_html__('Landscape', 'hyper-pwa'),
            ],
        ]);

        HYPWA_Settings::render('select', [
            'class'         => 'hypwa-select2',
            'id'            => 'hypwa_launch_mode_select_field',
            'name'          => 'hypwa_options[launch_mode]',
            'value'         => HYPWA_Options::get('launch_mode', 'standalone'),
            'label'         => esc_html__('Launch Mode', 'hyper-pwa'),
            'desc'          => esc_html__('Controls the display UI. Standalone hides browser bars to look like a native app.', 'hyper-pwa'),
            'options'       => [
                'full-screen' => esc_html__('Full Screen', 'hyper-pwa'),
                'standalone'  => esc_html__('Standalone', 'hyper-pwa'),
                'minimal-ui'  => esc_html__('Minimal UI', 'hyper-pwa'),
                'browser'     => esc_html__('Browser', 'hyper-pwa'),
            ],
        ]);

        HYPWA_Settings::render('color', [
            'class'         => '',
            'id'            => 'hypwa_theme_color_field',
            'name'          => 'hypwa_options[theme_color]',
            'value'         => HYPWA_Options::get('theme_color', '#2563eb'),
            'label'         => esc_html__('Theme Color', 'hyper-pwa'),
            'desc'          => esc_html__('Sets the primary color used for the browser UI and installed app. Choose a color that matches your brand..', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('color', [
            'class'         => '',
            'id'            => 'hypwa_background_color_field',
            'name'          => 'hypwa_options[background_color]',
            'value'         => HYPWA_Options::get('background_color', '#ffffff'),
            'label'         => esc_html__('Background Color', 'hyper-pwa'),
            'desc'          => esc_html__('Sets the background color of the app\'s splash screen while it loads. Choose a color that matches your app\'s background..', 'hyper-pwa'),
        ]);




    }

}