<?php

if (!defined('ABSPATH')) exit;

class HYPWA_Compatibility_Settings {

    public static function render() {

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_comp_one_signal_text_field',
            'name'          => 'hypwa_options[comp_one_signal]',
            'value'         => HYPWA_Options::get('comp_one_signal', '0'),            
            'label'         => esc_html__('OneSignal', 'hyper-pwa'),
            'desc'          => sprintf(
                esc_html__('Integrates Hyper PWA with OneSignal push notifications for reliable message delivery. %s', 'hyper-pwa'),
                '<a href="https://hyperpwa.com/knowledge-base/how-to-configure-onesignal-compatibility-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">' . esc_html__('Learn more', 'hyper-pwa') . ' <span class="dashicons dashicons-external"></span></a>'
            ),
        ]);

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_comp_webpushr_text_field',
            'name'          => 'hypwa_options[comp_webpushr]',
            'value'         => HYPWA_Options::get('comp_webpushr', '0'),            
            'label'         => esc_html__('Webpushr', 'hyper-pwa'),
            'desc'          => esc_html__('Connects Hyper PWA with Webpushr to support instant web push notifications.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_comp_gravitec_text_field',
            'name'          => 'hypwa_options[comp_gravitec]',
            'value'         => HYPWA_Options::get('comp_gravitec', '0'),            
            'label'         => esc_html__('Gravitec', 'hyper-pwa'),
            'desc'          => esc_html__('Provides integration with Gravitec push service for automated and personalized notifications.', 'hyper-pwa'),
        ]); 

        $caching_toggles = [
            'airlift'     => [
                'id'    => 'comp_airlift',
                'label' => esc_html__('Airlift', 'hyper-pwa'),
                'desc'  => esc_html__('Maintains compatibility with Airlift speed optimizer.', 'hyper-pwa'),
            ],
            'wp_rocket'   => [
                'id'    => 'comp_wp_rocket',
                'label' => esc_html__('WP Rocket', 'hyper-pwa'),
                'desc'  => esc_html__('Excludes PWA scripts from minification, JS deferment, and delay execution settings in WP Rocket.', 'hyper-pwa'),
            ],
            'litespeed'   => [
                'id'    => 'comp_litespeed',
                'label' => esc_html__('LiteSpeed Cache', 'hyper-pwa'),
                'desc'  => esc_html__('Excludes PWA scripts and manifest from being optimized, minified, or combined by LiteSpeed Cache.', 'hyper-pwa'),
            ],
            'autoptimize' => [
                'id'    => 'comp_autoptimize',
                'label' => esc_html__('Autoptimize', 'hyper-pwa'),
                'desc'  => esc_html__('Prevents Autoptimize from aggregating and minifying PWA bootstrap scripts.', 'hyper-pwa'),
            ],
            'wpfc'        => [
                'id'    => 'comp_wpfc',
                'label' => esc_html__('WP Fastest Cache', 'hyper-pwa'),
                'desc'  => esc_html__('Excludes PWA assets and requests from WP Fastest Cache optimization loops.', 'hyper-pwa'),
            ],
            'w3tc'        => [
                'id'    => 'comp_w3tc',
                'label' => esc_html__('W3 Total Cache', 'hyper-pwa'),
                'desc'  => esc_html__('Excludes service worker scripts and manifest files from W3 Total Cache minification and CDN.', 'hyper-pwa'),
            ],
            'wpsc'        => [
                'id'    => 'comp_wpsc',
                'label' => esc_html__('WP Super Cache', 'hyper-pwa'),
                'desc'  => esc_html__('Maintains service worker reliability by bypassing page cache storage for PWA request routes.', 'hyper-pwa'),
            ],
        ];

        foreach ( $caching_toggles as $key => $data ) {
            if ( defined( 'HYPWAP_VERSION' ) ) {
                HYPWA_Settings::render('checkbox', [
                    'id'    => 'hypwa_' . $data['id'] . '_text_field',
                    'name'  => 'hypwa_options[' . $data['id'] . ']',
                    'value' => HYPWA_Options::get($data['id'], '0'),            
                    'label' => $data['label'],
                    'desc'  => $data['desc'],
                ]);
            } else {
                ?>
                <div class="hypwa-form-row">
                    <div class="hypwa-label-col">
                        <label for="hypwa_<?php echo esc_attr($data['id']); ?>_text_field">
                            <?php echo esc_html($data['label']); ?> 
                            <span class="hypwa-premium-badge"><?php esc_html_e('Premium', 'hyper-pwa'); ?></span>
                            <a href="https://hyperpwa.com/premium/" target="_blank" class="hypwa-upgrade-badge"><?php esc_html_e('Upgrade', 'hyper-pwa'); ?></a>
                        </label>
                        <span class="hypwa-field-desc">
                            <?php echo esc_html($data['desc']); ?>
                        </span>
                    </div>
                    <div class="hypwa-input-col">
                        <div class="hypwa-controls-vertical-list">
                            <div class="hypwa-toggle-label-wrap hypwa-toggle-label-wrap-disabled">
                                <label class="hypwa-switch">
                                    <input type="checkbox" disabled />
                                    <span class="hypwa-slider hypwa-option-slider"></span>
                                </label>
                                <span class="hypwa-toggle-txt"><?php esc_html_e('OFF', 'hyper-pwa'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

    }

}