<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HYPWA_Settings_Ajax' ) ) {

    class HYPWA_Settings_Ajax {

        public function __construct() {
            // Save Settings
            add_action( 'wp_ajax_hypwa_save_settings', [ $this, 'save_settings' ] );

            // Reset Settings
            add_action( 'wp_ajax_hypwa_reset_settings', [ $this, 'reset_settings' ] );

            // Export Settings
            add_action( 'wp_ajax_hypwa_export_settings', [ $this, 'export_settings' ] );

            // Import Settings
            add_action( 'wp_ajax_hypwa_import_settings', [ $this, 'import_settings' ] );

            add_action( 'wp_ajax_hypwa_submit_support_ticket', [ $this, 'submit_support_ticket' ] );

            // Search Routes (Start, Offline, and 404 Pages)
            add_action( 'wp_ajax_hypwa_search_all_post_types', [ $this, 'search_all_post_types' ] );

            // Perform Migration
            add_action( 'wp_ajax_hypwa_perform_migration', [ $this, 'migrate_plugin_settings' ] );

            // Push Notifications AJAX
            add_action( 'wp_ajax_hypwa_push_connect', [ $this, 'push_connect' ] );
            add_action( 'wp_ajax_hypwa_push_disconnect', [ $this, 'push_disconnect' ] );
            add_action( 'wp_ajax_hypwa_send_push_notification', [ $this, 'send_push_notification' ] );
            add_action( 'wp_ajax_hypwa_push_refresh_stats', [ $this, 'push_refresh_stats' ] );
        }        
        

        public function submit_support_ticket() {

            check_ajax_referer( 'hypwa_submit_ticket', 'security' );

            $email = isset( $_POST['ticket_email'] )
                ? sanitize_email( wp_unslash( $_POST['ticket_email'] ) )
                : '';

            $ticket_subject = isset( $_POST['ticket_subject'] )
                ? sanitize_text_field( wp_unslash( $_POST['ticket_subject'] ) )
                : '';

            $description = isset( $_POST['detailed_description'] )
                ? sanitize_textarea_field( wp_unslash( $_POST['detailed_description'] ) )
                : '';

            if ( empty( $email ) || empty( $ticket_subject ) || empty( $description ) ) {
                wp_send_json_error(
                    esc_html__( 'All fields are required.', 'hyper-pwa' )
                );
            }

            if ( ! is_email( $email ) ) {
                wp_send_json_error(
                    esc_html__( 'Please enter a valid email address.', 'hyper-pwa' )
                );
            }

            $current_user = wp_get_current_user();

            $subject = sprintf(
                /* translators: %s: Ticket subject */
                esc_html__( 'Hyper PWA Support Ticket - %s', 'hyper-pwa' ),
                $ticket_subject
            );

            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'Reply-To: ' . $email,
            ];

            $body = sprintf(
                '
                <strong>Website Name:</strong> %1$s<br>
                <strong>Website URL:</strong> %2$s<br><br>

                <strong>User Name:</strong> %3$s<br>
                <strong>User Email:</strong> %4$s<br><br>

                <strong>Contact Email:</strong> %5$s<br><br>

                <strong>Ticket Subject:</strong> %6$s<br><br>

                <strong>Description:</strong><br>%7$s
                ',
                esc_html( get_bloginfo( 'name' ) ),
                esc_url( home_url() ),
                esc_html( $current_user->display_name ),
                esc_html( $current_user->user_email ),
                esc_html( $email ),
                esc_html( $ticket_subject ),
                nl2br( esc_html( $description ) )
            );

            $mail_sent = wp_mail(
                'support@hyperpwa.com',
                $subject,
                $body,
                $headers
            );

            if ( $mail_sent ) {
                wp_send_json_success(
                    esc_html__( 'Your support ticket has been submitted successfully.', 'hyper-pwa' )
                );
            }

            wp_send_json_error(
                esc_html__( 'Something went wrong. Please try again.', 'hyper-pwa' )
            );
        }

        public function save_settings() {

            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission to perform this action.', 'hyper-pwa' ) ]);
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason: Sanitization handled below in sanitize function.
            $raw_input = isset( $_POST['hypwa_options'] ) ? wp_unslash( $_POST['hypwa_options'] ) : [];

            if ( ! is_array( $raw_input ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'Invalid data received.', 'hyper-pwa' ) ]);
            }

            $sanitized = HYPWA_Options::sanitize( $raw_input );

            $existing = HYPWA_Options::get_options();
            $updated  = array_merge( $existing, $sanitized );

            update_option( HYPWA_Options::OPTION_KEY, $updated );

            // Check if static file serving is enabled and if the files were successfully created
            if ( isset( $updated['file_serving_method'] ) && 'static' === $updated['file_serving_method'] ) {
                $manifest_file = ABSPATH . ( function_exists( 'hypwa_manifest_filename' ) ? hypwa_manifest_filename() : 'hyper-pwa-manifest.json' );
                $sw_file       = ABSPATH . ( function_exists( 'hypwa_sw_filename' ) ? hypwa_sw_filename() : 'hyper-pwa-sw.js' );

                if ( ! file_exists( $manifest_file ) || ! file_exists( $sw_file ) ) {
                    wp_send_json_success([
                        'message' => __( 'Settings saved, but Hyper PWA was unable to write the static files to the root directory due to permission restrictions. Please check your folder permissions or use Dynamic serving.', 'hyper-pwa' )
                    ]);
                }
            }

            wp_send_json_success([ 'message' => __( 'Settings saved successfully.', 'hyper-pwa' ) ]);

        }

        public function reset_settings() {

            check_ajax_referer( 'hypwa_reset_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission to perform this action.', 'hyper-pwa' ) ]);
            }

            HYPWA_Options::reset();

            wp_send_json_success([ 'message' => esc_html__( 'Settings reset to defaults.', 'hyper-pwa' ) ]);
        }

        public function export_settings() {

            check_ajax_referer( 'hypwa_export_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission to perform this action.', 'hyper-pwa' ) ]);
            }

            $options = HYPWA_Options::get_options();

            wp_send_json_success([
                'message'  => __( 'Settings exported successfully.', 'hyper-pwa' ),
                'settings' => $options,
                'filename' => 'hypwa-settings-' . gmdate('Y-m-d') . '.json',
            ]);
        }

        public function import_settings() {

            check_ajax_referer( 'hypwa_import_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission to perform this action.', 'hyper-pwa' ) ]);
            }

            $json_string = isset( $_POST['settings_json'] ) ? sanitize_textarea_field( wp_unslash( $_POST['settings_json'] ) ) : '';

            if ( empty( $json_string ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'No import data provided.', 'hyper-pwa' ) ]);
            }

            $decoded = json_decode( $json_string, true );

            if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'Invalid JSON file. Please upload a valid settings export.', 'hyper-pwa' ) ]);
            }

            $sanitized    = HYPWA_Options::sanitize( $decoded );

            // Strip any keys not part of our defined options
            $allowed_keys = array_keys( HYPWA_Options::get_defaults() );
            $sanitized    = array_intersect_key( $sanitized, array_flip( $allowed_keys ) );

            // Merge with existing so nothing gets wiped
            $existing = HYPWA_Options::get_options();
            $updated  = array_merge( $existing, $sanitized );

            update_option( HYPWA_Options::OPTION_KEY, $updated );

            wp_send_json_success([ 'message' => esc_html__( 'Settings imported successfully.', 'hyper-pwa' ) ]);
        }

        public function search_all_post_types() {
            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission.', 'hyper-pwa' ) ]);
            }

            $search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';

            if ( empty( $search_term ) ) {
                wp_send_json_success([]);
            }

            $post_types = get_post_types([ 'public' => true ], 'names' );
            if ( isset( $post_types['attachment'] ) ) {
                unset( $post_types['attachment'] );
            }

            $args = [
                'post_type'      => array_values( $post_types ),
                'post_status'    => 'publish',
                's'              => $search_term,
                'posts_per_page' => 20,
            ];

            $search_query = new WP_Query( $args );
            $results      = [];

            if ( $search_query->have_posts() ) {
                while ( $search_query->have_posts() ) {
                    $search_query->the_post();
                    
                    $post_type_obj = get_post_type_object( get_post_type() );
                    $label         = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst( get_post_type() );

                    $results[] = [
                        'id'   => get_the_ID(), // Changed from relative URL to raw WordPress Post ID
                        'text' => get_the_title() . ' (' . $label . ')',
                    ];
                }
                wp_reset_postdata();
            }

            wp_send_json_success( $results );
        }

        public function migrate_plugin_settings() {

            check_ajax_referer( 'hypwa_migration_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error([ 'message' => esc_html__( 'You do not have permission to perform this action.', 'hyper-pwa' ) ]);
            }

            $migrate_plugin     =   isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

            if ( ! empty( $migrate_plugin ) ) {
                switch( $migrate_plugin ) {

                    case 'pwa': 

                            if ( ! function_exists ( 'pwaforwp_init_plugin' ) ) {
                                wp_send_json_error([ 'message' => esc_html__( 'Please activate the PWA plugin to continue with the migration.', 'hyper-pwa' ) ]);    
                            }

                            HYPWA_Migrate_Settings::migrate_pwa_plugin_settings();
                            wp_send_json_success([ 'message' => esc_html__( 'Settings migrated successfully.', 'hyper-pwa' ) ]);

                        break;

                    case 'super-pwa': 

                            if ( ! function_exists ( 'superpwa_activate_plugin' ) ) {
                                wp_send_json_error([ 'message' => esc_html__( 'Please activate the Super PWA plugin to continue with the migration.', 'hyper-pwa' ) ]);    
                            }

                            HYPWA_Migrate_Settings::migrate_super_pwa_plugin_settings();
                            wp_send_json_success([ 'message' => esc_html__( 'Settings migrated successfully.', 'hyper-pwa' ) ]);

                        break;

                }
            }

        }

        public function push_connect() {
            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized user.', 'hyper-pwa' ) ] );
            }

            $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
            if ( empty( $api_key ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'API Key is required.', 'hyper-pwa' ) ] );
            }

            $url = HYPWA_PUSH_API_BASE . '/api/v1/websites';
            $payload = [
                'name'   => get_bloginfo( 'name' ),
                'domain' => home_url(),
            ];

            $args = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'body'    => wp_json_encode( $payload ),
                'timeout' => 15,
            ];

            $response = wp_remote_post( $url, $args );

            if ( is_wp_error( $response ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Connection failed: ', 'hyper-pwa' ) . $response->get_error_message() ] );
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body        = wp_remote_retrieve_body( $response );
            $data        = json_decode( $body, true );

            if ( $status_code >= 200 && $status_code < 300 ) {
                $res_data = isset( $data['data'] ) ? $data['data'] : $data;
                if ( isset( $res_data['website'] ) && is_array( $res_data['website'] ) ) {
                    $res_data = array_merge( $res_data, $res_data['website'] );
                }

                $website_id   = ! empty( $res_data['site_id'] ) ? sanitize_text_field( $res_data['site_id'] ) : ( ! empty( $res_data['site_key'] ) ? sanitize_text_field( $res_data['site_key'] ) : ( ! empty( $res_data['id'] ) ? sanitize_text_field( $res_data['id'] ) : '' ) );
                $website_uuid = ! empty( $res_data['id'] ) ? sanitize_text_field( $res_data['id'] ) : '';

                HYPWA_Options::set( 'cf_push_api_key', $api_key );
                HYPWA_Options::set( 'cf_push_website_id', $website_id );
                HYPWA_Options::set( 'cf_push_website_uuid', $website_uuid );
                HYPWA_Options::set( 'cf_push_connected', '1' );

                delete_transient( 'hypwa_push_stats_cache' );

                if ( function_exists( 'hypwa_sync_static_files' ) ) {
                    hypwa_sync_static_files();
                }

                wp_send_json_success( [
                    'message'    => esc_html__( 'Connected successfully.', 'hyper-pwa' ),
                    'website_id' => $website_id,
                ] );
            } else {
                $err_msg = isset( $data['message'] ) ? $data['message'] : esc_html__( 'Invalid API Key or server error.', 'hyper-pwa' );
                wp_send_json_error( [ 'message' => $err_msg ] );
            }
        }

        public function push_disconnect() {
            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized user.', 'hyper-pwa' ) ] );
            }

            $api_key      = HYPWA_Options::get( 'cf_push_api_key' );
            $website_id   = HYPWA_Options::get( 'cf_push_website_id' );
            $website_uuid = HYPWA_Options::get( 'cf_push_website_uuid' );

            // If we have credentials, notify the server to make the website inactive
            if ( ! empty( $api_key ) && ( ! empty( $website_id ) || ! empty( $website_uuid ) ) ) {
                $target_id = ! empty( $website_uuid ) ? $website_uuid : $website_id;
                $url       = HYPWA_PUSH_API_BASE . '/api/v1/websites/' . $target_id;
                $payload   = [
                    'status' => 'inactive',
                ];

                $args = [
                    'method'  => 'PUT',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $api_key,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'body'    => wp_json_encode( $payload ),
                    'timeout' => 15,
                ];

                wp_remote_request( $url, $args );
            }

            HYPWA_Options::set( 'cf_push_api_key', '' );
            HYPWA_Options::set( 'cf_push_website_id', '' );
            HYPWA_Options::set( 'cf_push_website_uuid', '' );
            HYPWA_Options::set( 'cf_push_connected', '0' );

            delete_transient( 'hypwa_push_stats_cache' );

            if ( function_exists( 'hypwa_sync_static_files' ) ) {
                hypwa_sync_static_files();
            }

            wp_send_json_success( [ 'message' => esc_html__( 'Disconnected successfully.', 'hyper-pwa' ) ] );
        }

        public function send_push_notification() {
            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized user.', 'hyper-pwa' ) ] );
            }

            $connected = HYPWA_Options::get( 'cf_push_connected', '0' );
            if ( '1' !== $connected ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Not connected to hyperpushx.com.', 'hyper-pwa' ) ] );
            }

            $title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
            $message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
            $url     = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : home_url();
            $image   = isset( $_POST['image'] ) ? esc_url_raw( wp_unslash( $_POST['image'] ) ) : '';

            if ( empty( $title ) || empty( $message ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Title and Message are required.', 'hyper-pwa' ) ] );
            }

            $api_key      = HYPWA_Options::get( 'cf_push_api_key' );
            $website_uuid = HYPWA_Options::get( 'cf_push_website_uuid' );
            $website_id   = HYPWA_Options::get( 'cf_push_website_id' );

            $api_url = HYPWA_PUSH_API_BASE . '/api/v1/campaigns';
            $payload = [
                'website_id'    => ! empty( $website_uuid ) ? $website_uuid : $website_id,
                'title'         => $title,
                'message'       => $message,
                'url'           => $url,
                'delivery_type' => 'immediate',
            ];

            if ( ! empty( $image ) ) {
                $payload['image'] = $image;
                $payload['icon']  = $image;
            }

            $args = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
                'body'    => wp_json_encode( $payload ),
                'timeout' => 15,
            ];

            $response = wp_remote_post( $api_url, $args );

            if ( is_wp_error( $response ) ) {
                wp_send_json_error( [
                    'message' => esc_html__( 'Could not reach hyperpushx.com. Please check your internet connection.', 'hyper-pwa' ),
                ] );
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body        = wp_remote_retrieve_body( $response );
            $data        = json_decode( $body, true );

            // 201 Created — campaign dispatched successfully
            if ( $status_code === 201 ) {
                $campaign_status = isset( $data['status'] ) ? $data['status'] : '';
                $msg = esc_html__( 'Notification sent successfully!', 'hyper-pwa' );
                if ( $campaign_status === 'completed' ) {
                    $msg = esc_html__( 'Notification dispatched and delivered successfully!', 'hyper-pwa' );
                } elseif ( ! empty( $campaign_status ) ) {
                    /* translators: %s: campaign status string */
                    $msg = sprintf( esc_html__( 'Notification sent. Status: %s', 'hyper-pwa' ), esc_html( $campaign_status ) );
                }
                wp_send_json_success( [ 'message' => $msg ] );
            }

            // Determine the best error message from the API body
            $api_error = '';
            if ( ! empty( $data['error'] ) ) {
                $api_error = $data['error'];
            } elseif ( ! empty( $data['message'] ) ) {
                $api_error = $data['message'];
            } elseif ( ! empty( $data['errors'] ) && is_array( $data['errors'] ) ) {
                $api_error = implode( ' ', array_values( $data['errors'] ) );
            }

            if ( $status_code === 403 ) {
                $err = ! empty( $api_error )
                    ? $api_error
                    : esc_html__( 'Access denied. Your plan may have expired or a limit has been reached. Please check your hyperpushx.com account.', 'hyper-pwa' );
                wp_send_json_error( [ 'message' => sanitize_text_field( $err ) ] );
            }

            if ( $status_code === 401 ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Invalid API Key. Please reconnect your site to hyperpushx.com.', 'hyper-pwa' ) ] );
            }

            if ( $status_code === 422 ) {
                $err = ! empty( $api_error )
                    ? $api_error
                    : esc_html__( 'The notification data is invalid. Please check the title, message, and URL fields.', 'hyper-pwa' );
                wp_send_json_error( [ 'message' => sanitize_text_field( $err ) ] );
            }

            if ( $status_code === 429 ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Too many requests. Please wait a moment before sending another notification.', 'hyper-pwa' ) ] );
            }

            // Generic fallback for any other non-2xx status
            $fallback = ! empty( $api_error )
                ? $api_error
                : sprintf(
                    /* translators: %d: HTTP status code */
                    esc_html__( 'Unexpected error from hyperpushx.com (HTTP %d). Please try again.', 'hyper-pwa' ),
                    (int) $status_code
                );
            wp_send_json_error( [ 'message' => sanitize_text_field( $fallback ) ] );
        }

        public function push_refresh_stats() {
            check_ajax_referer( 'hypwa_save_settings_nonce', 'nonce' );

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized user.', 'hyper-pwa' ) ] );
            }

            delete_transient( 'hypwa_push_stats_cache' );

            if ( ! class_exists( 'HYPWA_Core_Feature_Settings' ) ) {
                require_once HYPWA_PLUGIN_DIR_PATH . 'includes/admin/settings/class-hypwa-core-feature-settings.php';
            }

            if ( class_exists( 'HYPWA_Core_Feature_Settings' ) ) {
                $stats = HYPWA_Core_Feature_Settings::get_subscriber_stats();
                if ( is_array( $stats ) ) {
                    wp_send_json_success( [
                        'total'   => number_format_i18n( $stats['total'] ),
                        'active'  => number_format_i18n( $stats['active'] ),
                        'expired' => number_format_i18n( $stats['expired'] ),
                    ] );
                }
            }

            wp_send_json_error( [ 'message' => esc_html__( 'Failed to fetch statistics.', 'hyper-pwa' ) ] );
        }
    }

    new HYPWA_Settings_Ajax();

}