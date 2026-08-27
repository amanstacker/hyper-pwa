<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HYPWA_Premium_Feature_Settings {

    public static function render() {

        $accordions          = self::get_accordions();
        $pro_plugin_active   = defined( 'HYPWAP_VERSION' );
        $pro_license_active  = self::is_pro_license_active();
        $pro_unlocked        = $pro_plugin_active && $pro_license_active;

        foreach ( $accordions as $accordion ) {

            $has_fields    = ! empty( $accordion['fields'] );
            $toggle_option = '0';
            $toggle_name   = '';

            if ( $has_fields ) {
                $toggle_field  = $accordion['fields'][0];
                $toggle_option = HYPWA_Options::get( $toggle_field['id'], 1 );
                $toggle_name   = $toggle_field['name'];
            }

            // Fields render (toggle + card-content) only when Pro is active AND the license is valid.
            $render_fields_ui = $pro_unlocked && $has_fields;

            $card_classes   = [ 'hypwa-card' ];
            if ( ! $render_fields_ui ) {
                $card_classes[] = 'hypwa-pro-locked-card';
            }
            ?>
            <div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
                <div class="hypwa-card-header">

                    <div class="hypwa-card-title-block">
                        <div class="hypwa-card-icon purple-icon">
                            <span class="dashicons <?php echo esc_attr( $accordion['icon'] ); ?>"></span>
                        </div>
                        <div>
                            <h3><?php echo esc_html( $accordion['title'] ); ?></h3>
                            <p><?php echo esc_html( $accordion['desc'] ); ?></p>
                        </div>
                    </div>

                    <?php if ( $render_fields_ui ) : ?>

                        <div class="hypwa-card-actions">
                            <?php if ( ! empty( $accordion['doc_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $accordion['doc_link'] ); ?>" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                                    <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                            <?php endif; ?>
                            <div class="hypwa-toggle-label-wrap">
                                <label class="hypwa-switch">
                                    <input type="hidden" name="<?php echo esc_attr( $toggle_name ); ?>" value="0">
                                    <input type="checkbox" name="<?php echo esc_attr( $toggle_name ); ?>" value="1" <?php checked( $toggle_option, '1' ); ?>>
                                    <span class="hypwa-slider"></span>
                                </label>
                                <span class="hypwa-toggle-txt">
	                                <?php echo $toggle_option ? esc_html__( 'ON', 'hyper-pwa' ) : esc_html__( 'OFF', 'hyper-pwa' ); ?>
                                </span>
                            </div>
                            <span class="dashicons dashicons-arrow-down-alt2 hypwa-chevron"></span>
                        </div>

                    <?php else : ?>

                        <div class="hypwa-card-actions">
                            <?php if ( ! empty( $accordion['doc_link'] ) ) : ?>
                                <a href="<?php echo esc_url( $accordion['doc_link'] ); ?>" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                                    <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                            <?php endif; ?>
                            <span class="dashicons dashicons-arrow-down-alt2 hypwa-chevron"></span>
                        </div>

                    <?php endif; ?>

                </div>

                <?php if ( $render_fields_ui ) : ?>
                    <div class="hypwa-card-content">
                        <div class="hypwa-card-fields">
                            <?php
                            do_action( 'hypwa_pro_feature_fields', $accordion );

                            if ( ! empty( $accordion['doc_link'] ) ) {
                                do_action( 'hypwa_learnmore_doc', $accordion['doc_link'] );
                            }
                            ?>
                        </div>
                    </div>
                    <?php elseif ( ! empty( $accordion['preview_image'] ) ) : ?>
                        <?php
                        // Pro plugin installed but license not activated -> point to activation, not purchase.
                        if ( $pro_plugin_active && ! $pro_license_active ) {
                            $overlay_text  = esc_html__( 'Activate License', 'hyper-pwa' );
                            $overlay_title = esc_attr__( 'Activate your license to unlock', 'hyper-pwa' );
                            $overlay_link  = admin_url( 'admin.php?page=hypwa-license' );
                            $overlay_target = '_self';
                        } else {
                            $overlay_text  = esc_html__( 'Upgrade to Pro', 'hyper-pwa' );
                            $overlay_title = esc_attr__( 'Upgrade to Pro to Unlock', 'hyper-pwa' );
                            $overlay_link  = 'https://hyperpwa.com/premium/';
                            $overlay_target = '_blank';
                        }
                        ?>
                        <div class="hypwa-card-content">
                            <div class="hypwa-card-fields hypwa-fields-disabled hypwa-image-preview">
                                <img src="<?php echo esc_url( $accordion['preview_image'] ); ?>" alt="<?php echo esc_attr( $accordion['title'] ); ?>" loading="lazy">
                                <div class="hypwa-lock-overlay" title="<?php echo $overlay_title; ?>">
                                    <span class="dashicons dashicons-lock"></span>
                                    <a href="<?php echo esc_url( $overlay_link ); ?>" class="hypwa-upgrade-link hypwa-overlay-upgrade-link" target="<?php echo esc_attr( $overlay_target ); ?>" rel="noopener noreferrer">
                                        <?php echo $overlay_text; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

            </div>
            <?php
        }
    }

    /**
     * Checks whether the Hyper PWA Premium license is active/valid.
     *
     * This reads the same 'hypwa_license_data' option that the Pro plugin's
     * license.php stores the EDD license-check response in, so it works
     * even though the Free plugin has no knowledge of the Pro plugin's classes.
     *
     * @return bool
     */
    private static function is_pro_license_active() {
        $license_data = get_option( 'hypwa_license_data' );

        return ! empty( $license_data['license'] ) && 'valid' === $license_data['license'];
    }

    private static function get_accordions() {
        $accordions     =    [
            [
                'handler'    => 'custom_install_app',
                'id'    => 'custom_install_app',
                'title' => esc_html__( 'Custom Install App', 'hyper-pwa' ),
                'desc'  => esc_html__( "Replace the browser's default install prompt with a fully branded install experience.", 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-the-install-app-popup-in-hyper-pwa/',
                'icon'  => 'dashicons-download',
                'fields' => [],
            ],
            [
                'handler'    => 'app_shortcuts',
                'title' => esc_html__( 'App Shortcuts', 'hyper-pwa' ),
                'desc'  => esc_html__( "Add quick-action shortcuts to your PWA's home screen icon for instant page access.", 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-create-app-shortcuts-in-hyper-pwa/',
                'icon'  => 'dashicons-shortcode',
                'fields' => [],
            ],
            [
                'handler'    => 'initial_loader',
                'id'    => 'initial_loader',
                'title' => esc_html__( 'Initial Loader', 'hyper-pwa' ),
                'desc'  => esc_html__( 'Display a branded splash screen while your PWA loads for a native app-like experience.', 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-the-initial-loader-in-hyper-pwa/',
                'icon'  => 'dashicons-image-rotate',
                'fields' => [],
            ],
            [
                'handler'    => 'bottom_navigation',
                'id'    => 'bottom_navigation',
                'title' => esc_html__( 'Bottom Navigation', 'hyper-pwa' ),
                'desc'  => esc_html__( 'Add a fixed bottom nav bar for thumb-friendly mobile navigation across key pages.', 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-create-a-bottom-navigation-menu-in-hyper-pwa/',
                'icon'  => 'dashicons-menu-alt',
                'fields' => [],
            ],
            [
                'handler'    => 'multilingual',
                'id'    => 'multilingual',
                'title' => esc_html__( 'Multilingual', 'hyper-pwa' ),
                'desc'  => esc_html__( "Serve your PWA in multiple languages based on the user's locale or preference.", 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-create-a-multilingual-progressive-web-app-with-hyper-pwa/',
                'icon'  => 'dashicons-translation',
                'fields' => [],
            ],
            [
                'handler'    => 'app_install_qr',
                'id'    => 'app_install_qr',
                'title' => esc_html__( 'App Install QR', 'hyper-pwa' ),
                'desc'  => esc_html__( "Scan the QR code to open a dedicated page to install the app.", 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-generate-qr-codes-for-pwa-installation/',
                'icon'  => 'dashicons dashicons-smartphone',
                'fields' => [],
            ],
            [
                'handler'    => 'offline_forms',
                'id'    => 'offline_forms',
                'title' => esc_html__( 'Offline Forms', 'hyper-pwa' ),
                'desc'  => esc_html__( 'Capture and queue form submissions when offline, syncing automatically on reconnect.', 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/',
                'icon'  => 'dashicons-feedback',
                'fields' => [],
            ],
            [
                'handler'    => 'smart_analytics',
                'id'    => 'smart_analytics',
                'title' => esc_html__( 'Smart Analytics', 'hyper-pwa' ),
                'desc'  => esc_html__( 'Track PWA installs, launches, and user engagement with actionable insights.', 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-configure-and-use-smart-analytics-in-hyper-pwa/',
                'icon'  => 'dashicons-chart-line',
                'fields' => [],
            ],
            [
                'handler'    => 'android_apk_builder',
                'id'    => 'android_apk_builder',
                'title' => esc_html__( 'Android APK Builder', 'hyper-pwa' ),
                'desc'  => esc_html__( 'Generate a ready-to-publish Android APK from your PWA without writing any code.', 'hyper-pwa' ),
                'doc_link' => 'https://hyperpwa.com/knowledge-base/how-to-build-an-android-apk-with-hyper-pwa-and-publish-it-on-google-play/',
                'icon'  => 'dashicons-admin-plugins',
                'fields' => [],
            ],
        ];

        // Static preview screenshot shown (faded, locked) when the Pro plugin isn't installed at all.
        foreach ( $accordions as $key => $accordion ) {
            $accordions[ $key ]['preview_image'] = HYPWA_DIR_URI . 'assets/admin/img/previews/' . $accordion['handler'] . '.png';
        }

        $accordions     =   apply_filters( 'hypwa_premium_feature_fields',  $accordions );

        return $accordions;
    }

}