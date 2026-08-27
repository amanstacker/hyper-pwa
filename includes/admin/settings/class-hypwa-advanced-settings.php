<?php

if (!defined('ABSPATH')) exit;

class HYPWA_Advanced_Settings {

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
        HYPWA_Settings::render('select', [
            'class'         => 'hypwa-select2',
            'id'            => 'hypwa_file_serving_method_select_field',
            'name'          => 'hypwa_options[file_serving_method]',
            'value'         => HYPWA_Options::get('file_serving_method', 'dynamic'),
            'label'         => esc_html__('File Serving Method', 'hyper-pwa'),
            'desc'          => sprintf(
                esc_html__('Server and hosting configurations vary. If your server doesn\'t support WordPress rewrite rules or restricts writing files to the root directory, choose the alternative serving method. %s', 'hyper-pwa'),
                '<a href="https://hyperpwa.com/knowledge-base/how-to-fix-service-worker-and-manifest-errors-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">' . esc_html__('Learn more', 'hyper-pwa') . ' <span class="dashicons dashicons-external"></span></a>'
            ),
            'options'       => [
                'dynamic'                   => esc_html__('Dynamic - Generated on the fly', 'hyper-pwa'),
                'static'                    => esc_html__('Static - Stored in root directory', 'hyper-pwa'),
            ],
        ]);
        HYPWA_Settings::render('text', [
            'id'            => 'hypwa_force_update_text_field',
            'name'          => 'hypwa_options[force_update]',
            'value'         => HYPWA_Options::get('force_update', '1.0'),
            'placeholder'   => '1.0',
            'label'         => esc_html__('Force Update', 'hyper-pwa'),
            'desc'          => esc_html__('Update this version number (e.g. from 1.0 to 1.1) to force the PWA to update and refresh the browser cache for all visitors.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('textarea', [
            'id'            => 'hypwa_custom_install_trigger_text_field',
            'name'          => 'hypwa_options[custom_install_trigger]',
            'value'         => HYPWA_Options::get('custom_install_trigger'),
            'placeholder'   => '.install-pwa-btn, #my-custom-install-button, a.btn-download',
            'label'         => esc_html__('Custom Install Trigger', 'hyper-pwa'),
            'desc'          => esc_html__('Enter a comma-separated list of CSS selectors (e.g. classes or IDs) for HTML elements on your site. When clicked, these elements will trigger the PWA installation prompt.', 'hyper-pwa'),
        ]);        

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_cf_gutenberg_block_status',
            'name'          => 'hypwa_options[cf_gutenberg_block_status]',
            'value'         => HYPWA_Options::get('cf_gutenberg_block_status', '0'),            
            'label'         => esc_html__('Gutenberg Block', 'hyper-pwa'),
            'desc'          => sprintf(
                esc_html__('Enable or disable the custom PWA Install Button Gutenberg block in the WordPress block editor. %s', 'hyper-pwa'),
                '<a href="https://hyperpwa.com/knowledge-base/gutenberg-block-guide-pwa-install-button/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">' . esc_html__('Learn more', 'hyper-pwa') . ' <span class="dashicons dashicons-external"></span></a>'
            ),
        ]);

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_cf_elementor_widget_status',
            'name'          => 'hypwa_options[cf_elementor_widget_status]',
            'value'         => HYPWA_Options::get('cf_elementor_widget_status', '0'),            
            'label'         => esc_html__('Elementor Widget', 'hyper-pwa'),
            'desc'          => esc_html__('Enable or disable the custom PWA Install Button widget in the Elementor page builder.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_preload_app_manifiest_text_field',
            'name'          => 'hypwa_options[preload_app_manifiest]',
            'value'         => HYPWA_Options::get('preload_app_manifiest'),            
            'label'         => esc_html__('Preload App Manifest', 'hyper-pwa'),
            'desc'          => esc_html__('An abbreviated name (max 12 characters) used where display space is limited.', 'hyper-pwa'),
        ]);

        HYPWA_Settings::render('checkbox', [
            'id'            => 'hypwa_cache_external_text_field',
            'name'          => 'hypwa_options[cache_external]',
            'value'         => HYPWA_Options::get('cache_external'),            
            'label'         => esc_html__('Cache External Resources', 'hyper-pwa'),
            'desc'          => esc_html__('An abbreviated name (max 12 characters) used where display space is limited.', 'hyper-pwa'),
        ]);

        if ( defined( 'HYPWAP_VERSION' ) ) {
            HYPWA_Settings::render('checkbox', [
                'id'            => 'hypwa_pf_link_hover_prefetch_status',
                'name'          => 'hypwa_options[pf_link_hover_prefetch_status]',
                'value'         => HYPWA_Options::get('pf_link_hover_prefetch_status', '0'),            
                'label'         => esc_html__('Link Hover Prefetching', 'hyper-pwa'),
                'desc'          => sprintf(
                    esc_html__('Instant Page Transitions: Pre-cache pages in the service worker cache the moment a user hovers over a link, reducing page transition latency to 0ms. %s', 'hyper-pwa'),
                    '<a href="https://hyperpwa.com/knowledge-base/how-to-use-link-hover-prefetching-for-instant-page-transitions-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">' . esc_html__('Learn more', 'hyper-pwa') . ' <span class="dashicons dashicons-external"></span></a>'
                ),
            ]);
        } else {
            ?>
            <div class="hypwa-form-row">
                <div class="hypwa-label-col">
                    <label for="hypwa_pf_link_hover_prefetch_status">
                        <?php esc_html_e('Link Hover Prefetching', 'hyper-pwa'); ?> 
                        <span class="hypwa-premium-badge"><?php esc_html_e('Premium', 'hyper-pwa'); ?></span>
                        <a href="https://hyperpwa.com/premium/" target="_blank" class="hypwa-upgrade-badge"><?php esc_html_e('Upgrade', 'hyper-pwa'); ?></a>
                    </label>
                    <span class="hypwa-field-desc">
                        <?php esc_html_e('Instant Page Transitions: Pre-cache pages in the service worker cache the moment a user hovers over a link, reducing page transition latency to 0ms.', 'hyper-pwa'); ?>
                        <a href="https://hyperpwa.com/knowledge-base/how-to-use-link-hover-prefetching-for-instant-page-transitions-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                            <?php esc_html_e('Learn more', 'hyper-pwa'); ?>
                            <span class="dashicons dashicons-external"></span>
                        </a>
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

        if ( defined( 'HYPWAP_VERSION' ) ) {
            HYPWA_Settings::render('checkbox', [
                'id'            => 'hypwa_fix_mixed_content_text_field',
                'name'          => 'hypwa_options[fix_mixed_content]',
                'value'         => HYPWA_Options::get('fix_mixed_content', '0'),            
                'label'         => esc_html__('Fix Mixed Content', 'hyper-pwa'),
                'desc'          => sprintf(
                    esc_html__('Automatically upgrades HTTP resource requests to HTTPS and rewrites mixed content page assets. Note: This requires an SSL certificate (HTTPS) to be installed on your domain first. %s', 'hyper-pwa'),
                    '<a href="https://hyperpwa.com/knowledge-base/how-to-fix-ssl-and-mixed-content-errors-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">' . esc_html__('Learn more', 'hyper-pwa') . ' <span class="dashicons dashicons-external"></span></a>'
                ),
            ]); 
        } else {
            ?>
            <div class="hypwa-form-row">
                <div class="hypwa-label-col">
                    <label for="hypwa_fix_mixed_content_text_field">
                        <?php esc_html_e('Fix Mixed Content', 'hyper-pwa'); ?> 
                        <span class="hypwa-premium-badge"><?php esc_html_e('Premium', 'hyper-pwa'); ?></span>
                        <a href="https://hyperpwa.com/premium/" target="_blank" class="hypwa-upgrade-badge"><?php esc_html_e('Upgrade', 'hyper-pwa'); ?></a>
                    </label>
                    <span class="hypwa-field-desc">
                        <?php esc_html_e('Automatically upgrades HTTP resource requests to HTTPS and rewrites mixed content page assets. Note: This requires an SSL certificate (HTTPS) to be installed on your domain first.', 'hyper-pwa'); ?>
                        <a href="https://hyperpwa.com/knowledge-base/how-to-fix-ssl-and-mixed-content-errors-in-hyper-pwa/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                            <?php esc_html_e('Learn more', 'hyper-pwa'); ?>
                            <span class="dashicons dashicons-external"></span>
                        </a>
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