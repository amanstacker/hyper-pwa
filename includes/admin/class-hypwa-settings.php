<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HYPWA_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'hypwa_add_menu_pages' ] );        
        add_action( 'admin_enqueue_scripts', [ $this, 'hypwa_enqueue_admin_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'hypwa_dequeue_conflicting_scripts' ], 999 );
        add_action( 'hypwa_learnmore_doc', [ $this, 'render_learnmore' ], 10, 1 );
    }

    public function hypwa_add_menu_pages() {
        $menu_icon = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSI1LjUgMjQuNzUgODcuMjUgNDcuMjUiCiA+CjxtZXRhZGF0YT4KQ3JlYXRlZCBieSBwb3RyYWNlIDEuMTYsIHdyaXR0ZW4gYnkgUGV0ZXIgU2VsaW5nZXIgMjAwMS0yMDE5CjwvbWV0YWRhdGE+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLDEwMC4wMDAwMDApIHNjYWxlKDAuMDI1MDAwLC0wLjAyNTAwMCkiCmZpbGw9IiNmZmZmZmYiIHN0cm9rZT0ibm9uZSI+CjxwYXRoIGQ9Ik0xNTEwIDI5NjAgYy0zNiAtNCAtNzYgLTEwIC05MCAtMTQgLTQwIC0xMiAtMTMyIC01OSAtMTU3IC04MSAtNDYKLTM5IC0xMTMgLTExMCAtMTEzIC0xMTkgMCAtNSAtNyAtMTcgLTE0IC0yNSAtOCAtOSAtMjUgLTQ2IC0zOCAtODEgbC0yMyAtNjUKLTIyMCAtNSBjLTI1NSAtNiAtMjY1IC05IC0yNjUgLTczIDAgLTI0IDUgLTQ4IDEyIC01NSA5IC05IDc0IC0xMiAyMjkgLTEyCjEyMyAwIDIyMCAtNCAyMjQgLTkgMyAtNSAxIC0yMCAtNSAtMzMgLTYgLTEzIC0xNiAtNzUgLTIzIC0xMzggbC0xMyAtMTE1Ci0xNTAgLTMgYy0xNTEgLTIgLTE1MSAtMyAtMTYzIC0yOCAtMTkgLTQxIC0xMyAtNjMgMjIgLTg0IDI3IC0xOCA0OCAtMjAgMTQwCi0xOSA2NCAxIDExMyAtMyAxMTkgLTkgNyAtNyA0IC0zMCAtMTEgLTc0IC0xMSAtMzQgLTIxIC04NSAtMjIgLTExMyAtMSAtMjcKLTYgLTU2IC0xMiAtNjIgLTcgLTkgLTUxIC0xMyAtMTU4IC0xMyAtMTQxIDAgLTE0OSAtMSAtMTYzIC0yMiAtMjIgLTMyIC0yMAotNjQgNiAtODIgMTcgLTEzIDUxIC0xNiAxNDggLTE2IDEzNSAwIDE1MCAtNSAxNTAgLTUwIDEgLTQ1IDM5IC0xNDAgODEgLTIwNAo2NiAtOTggMTM3IC0xNTQgMjM5IC0xODUgNjMgLTIwIDg5IC0yMSA5ODUgLTIxIGw5MjAgMCA3NiAyNCBjOTMgMjkgMTE4IDQ0CjE5MSAxMTggMTA4IDEwOCAxNDIgMTkzIDE1OCAzOTMgNSA2MSAxNiAxMzUgMjQgMTY1IDkgMzAgMTYgNzggMTYgMTA1IDAgMjggOQoxMDAgMTkgMTYwIDMxIDE3NCA1NCA0MTAgNDggNDgzIC0xMCAxMjEgLTU1IDIwMiAtMTYxIDI4OCAtMTAxIDgyIC00NyA3OAotMTA1MSA4MCAtNDkwIDAgLTkxOSAtMiAtOTU1IC02eiBtMTg1NCAtMTYxIGM4OSAtMjggMTI4IC02OCAxNTYgLTE2MCAyMSAtNjcKMjEgLTcyIDYgLTE2NiAtMTggLTExMSAtMjggLTE5NCAtNTEgLTQwOCAtOSAtODIgLTIxIC0xNzkgLTI2IC0yMTUgLTYgLTM2Ci0xNCAtMTEyIC0xOCAtMTcwIC01IC02NyAtMTMgLTExNSAtMjMgLTEzMCAtOSAtMTQgLTE5IC0zNiAtMjMgLTQ4IC0xMSAtMzYKLTYzIC0xMDkgLTkzIC0xMzAgLTE1IC0xMSAtNTMgLTM0IC04NSAtNTEgbC01NyAtMzEgLTkyMyAwIGMtNzg1IDAgLTkyNiAyCi05NDcgMTUgLTE0IDggLTM0IDE4IC00NSAyNCAtNzYgMzYgLTEyNSA5OCAtMTUxIDE5MCBsLTE3IDY0IDMxIDI2IGMzOSAzMSA0NAo4MiAxMiAxMTYgLTE5IDIwIC0xOSAyNCAtNCAxMjAgMjEgMTQ1IDI2IDE1OCA2MCAxNzQgNTEgMjUgNTYgNDUgMTkgODkgLTI1CjMwIC0yNSAzMiAtMTUgMTIxIDUgNDkgMTUgMTAyIDIwIDExNiA2IDE1IDEwIDM3IDEwIDUxIDAgMTggOSAyNyAzNSAzOCA0NiAyMAo2NCA1NyA0MSA4OCAtNTAgNzAgLTQ4IDYyIC0zMCAxMDQgMzcgOTAgMTAyIDE0MSAyMjIgMTc0IDYyIDE3IDEyOCAxOCA5NDggMTkKODcwIDEgODgxIDEgOTQ4IC0yMHoiLz4KPHBhdGggZD0iTTE0MzAgMjU2MyBjLTI0IC05IC00MCAtMzUgLTQwIC02NyAwIC0xNyAtOSAtNjYgLTE5IC0xMTEgLTIyIC05MQotNTcgLTMwOCAtNjcgLTQwNSAtMyAtMzYgLTEyIC04NSAtMTkgLTExMCAtNyAtMjUgLTE2IC04MiAtMjAgLTEyNyAtNiAtNzcgLTUKLTgyIDE3IC05NyAyNSAtMTggMTEyIC0yMiAxNTIgLTYgMzAgMTEgNTYgNTcgNTYgOTkgMCA0MyAzMSAxOTQgNDMgMjA5IDUgNwozMyAxMiA2OCAxMiAzMyAwIDg4IDEwIDEyNiAyMiA1NyAxOCA3OCAzMiAxMzAgODUgMzQgMzQgNjQgNTkgNjYgNTUgMyAtNCAxMAotMTA2IDE3IC0yMjcgMTAgLTE2OCAxNiAtMjI0IDI4IC0yMzcgMTIgLTE1IDMxIC0xOCA5OCAtMTggbDgzIDAgMzEgNDQgYzE2CjI1IDMwIDQ5IDMwIDU1IDAgMTMgNDYgMTM0IDY2IDE3NCA4IDE2IDE0IDM0IDE0IDQxIDAgOCAxMyA0MiAyOSA3NyAyOCA2MSAzMAo2MyAzNiAzNiA0IC0xNSA1IC0zNyAyIC01MCAtOSAtMzYgMjkgLTM0NyA0NCAtMzYzIDggLTggNDEgLTE0IDgwIC0xNiA3NSAtMgoxMTMgMTUgMTMwIDYwIDYgMTUgMTkgNDUgMjkgNjcgMTAgMjIgMzEgNzIgNDUgMTEwIDE1IDM5IDQwIDk3IDU1IDEzMCAzMSA2OAo3NSAxNzAgOTcgMjMwIDkgMjIgMjcgNjUgNDAgOTUgNDcgMTAyIDY3IDE0NSA4MiAxNzcgMjUgNTIgNyA2MyAtMTA1IDYzIGwtOTMKMCAtMjYgLTM3IGMtMTQgLTIxIC0yNSAtNDQgLTI1IC01MSAwIC04IC04IC0zMSAtMTggLTUxIC00OSAtOTcgLTk2IC0yMTYKLTEwNyAtMjY4IC00IC0xOCAtMTIgLTMzIC0xOSAtMzMgLTE2IDAgLTI2IDEwNiAtMjYgMjg1IDAgODggLTQgMTM1IC0xMiAxNDMKLTcgNyAtNDQgMTIgLTk1IDEyIC03MCAwIC04NiAtMyAtMTAzIC0yMCAtMTEgLTExIC0yMCAtMjggLTIwIC0zNyAwIC05IC05Ci0zNyAtMjEgLTYyIC0yMiAtNTAgLTU0IC0xMTkgLTg4IC0xOTIgLTEyIC0yNSAtMjEgLTUxIC0yMSAtNTggMCAtMjMgLTMyCi0xMTEgLTQxIC0xMTEgLTUgMCAtMTAgMTA3IC0xMSAyMzggbC0zIDIzNyAtMTEwIDAgLTExMCAwIC0zIC0zOCAtMyAtMzcgLTI1CjE5IGMtNjAgNDcgLTEyNSA2MSAtMjgyIDYwIC04MSAwIC0xNTQgLTMgLTE2MiAtNnogbTI3OSAtMjA4IGMzNSAtMTcgMzkgLTI4CjMzIC04MyAtNiAtNjIgLTQzIC05NyAtMTExIC0xMDYgLTMwIC00IC01NyAtMyAtNjIgMSAtOCA5IDUgMTc2IDE2IDE5MyA5IDE1CjkyIDEyIDEyNCAtNXoiLz4KPHBhdGggZD0iTTMwMzMgMjU1OCBjLTYgLTcgLTIyIC0zOCAtMzYgLTY4IC0xNCAtMzAgLTQxIC04OSAtNjAgLTEzMSAtMjAgLTQxCi0zOSAtODcgLTQyIC0xMDIgLTQgLTE1IC0xMSAtMzAgLTE2IC0zMyAtNSAtMyAtOSAtMTMgLTkgLTIyIDAgLTkgLTE3IC01MwotMzkgLTk3IC01OCAtMTE5IC05MSAtMTkyIC05MSAtMjA1IDAgLTYgLTE4IC00OCAtMzkgLTk0IC01MiAtMTA5IC01NyAtMTI5Ci0zNiAtMTUwIDE0IC0xMyAzMyAtMTYgOTMgLTE0IGw3NiAzIDIyIDQwIGMxMyAyMiAyOCA1MSAzNCA2NSA2IDE0IDIwIDQ0IDMxCjY4IGwyMSA0MiAxMTQgMCBjNjMgMCAxMTQgLTMgMTE1IC03IDEgLTUgMyAtMjggNSAtNTMgMTIgLTE0OCAyMSAtMTYwIDEyNgotMTYwIDYwIDAgNjkgMyA4MyAyNCAxNCAyMSAxNCAzNyAwIDE2NyAtOSA3OSAtMjUgMTg4IC0zNiAyNDEgLTEwIDUzIC0xOSAxMTQKLTE5IDEzNiAwIDIxIC03IDYxIC0xNiA4OCAtOCAyNyAtMTkgOTMgLTIzIDE0NiAtNCA1NCAtMTMgMTA0IC0yMCAxMTIgLTE3IDIxCi0yMjMgMjMgLTIzOCA0eiBtMTAyIC0zODcgYzkgLTM4IDE1IC04MCAxMyAtOTIgLTMgLTIyIC04IC0yNCAtNjUgLTI2IC02NSAtMgotODEgMTAgLTQ4IDM3IDggNyAxNSAxOCAxNSAyNCAwIDIwIDU0IDEzNSA2MSAxMzAgNCAtMiAxNCAtMzUgMjQgLTczeiIvPgo8cGF0aCBkPSJNMTYyNiAxNTUzIGMtMTMgLTQ2IC0yOSAtMTcyIC0yMyAtMTg2IDggLTIwIDYwIC0yMyA3NSAtNCA1IDYgMTIgMjMKMTQgMzcgMiAxNyA5IDI1IDIzIDI1IDE2IDAgMjAgLTcgMjAgLTM1IDAgLTMyIDMgLTM1IDMzIC0zOCAyNyAtMyAzNCAxIDQyIDIzCjYgMTQgMTAgNDMgMTAgNjQgMCAyMSA1IDUyIDExIDcwIDE0IDQwIC0xIDYxIC00MyA2MSAtMjQgMCAtMzIgLTYgLTQ0IC0zNQotMTYgLTQwIC0zNCAtNDYgLTM0IC0xMiAwIDMxIC0xOCA0NyAtNTEgNDcgLTE4IDAgLTI5IC02IC0zMyAtMTd6Ii8+CjxwYXRoIGQ9Ik0xOTExIDE1NTYgYy0xMCAtMTIgLTcgLTI1IDEzIC02MyAxOCAtMzIgMjYgLTY0IDI2IC05NiBsMCAtNDggMzggMwpjMzQgMyAzOCA2IDUyIDQ5IDggMjUgMzAgNjQgNDggODcgNDEgNTIgMzkgNzYgLTcgODAgLTI3IDMgLTM5IC0yIC01MiAtMTkKbC0xNyAtMjIgLTExIDIxIGMtMTQgMjYgLTcxIDMxIC05MCA4eiIvPgo8cGF0aCBkPSJNMjE5MyAxNTU4IGMtMTEgLTEzIC0yNSAtMTA3IC0yNyAtMTY4IC0xIC0zNCAwIC0zNSAzOSAtMzUgMzggMCA0MAoyIDQ1IDM1IDUgMzMgNyAzNSA0NyAzOCA2OSA1IDEwNCA2NyA2NyAxMjAgLTEzIDE5IC0yNCAyMiAtODggMjIgLTQ3IDAgLTc3Ci01IC04MyAtMTJ6Ii8+CjxwYXRoIGQ9Ik0yNDUwIDE1MjQgYy0xMyAtMjggLTIwIC02OCAtMjAgLTEwNSAwIC01NiAyIC02MCAyOCAtNzAgMzIgLTEzIDEyOAotNyAxNDMgOCA2IDYgOCAyMCA0IDMyIC01IDE2IC0xNSAyMSAtNDEgMjEgLTE5IDAgLTM0IDUgLTM0IDEwIDAgNiA5IDEwIDIxCjEwIDM1IDAgNjEgMTkgNTQgNDAgLTMgMTEgMSAzMSAxMSA0NSAyNiA0MCA2IDU1IC03OCA1NSBsLTY5IDAgLTE5IC00NnoiLz4KPHBhdGggZD0iTTI3MTUgMTU1OCBjLTIgLTcgLTkgLTMxIC0xNCAtNTMgLTYgLTIyIC0xNiAtNTggLTIyIC04MSAtMTcgLTU4IC04Ci03NiAzOCAtNzIgMzMgMyAzOSA3IDQ3IDM2IDUgMTcgMTIgMzIgMTUgMzIgNCAwIDExIC0xNSAxNiAtMzIgOSAtMjkgMTUgLTMzCjQ3IC0zNiA0NiAtNCA1OCAyMyAzMiA2NyAtMTcgMjggLTE3IDMxIC0xIDQ5IDE3IDE5IDIyIDU3IDExIDg2IC01IDEzIC0yMiAxNgotODUgMTYgLTU1IDAgLTgxIC00IC04NCAtMTJ6Ii8+CjxwYXRoIGQ9Ik0xMjgxIDE1MTAgYy01NCAtMTMgLTY5IC01NCAtMjUgLTcwIDI4IC0xMSAyMjMgLTE0IDI0OCAtNCAyMyA5IDE5CjUwIC02IDY4IC0yNCAxNyAtMTU0IDIxIC0yMTcgNnoiLz4KPHBhdGggZD0iTTI5OTAgMTUwMCBjLTIzIC0yMyAtMjUgLTQxIC04IC01OCAxMyAtMTMgMjQyIC0xOCAyNzIgLTYgMjMgOSAyMQo2MSAtMyA3NCAtMTEgNSAtNjkgMTAgLTEzMCAxMCAtOTggMCAtMTEzIC0yIC0xMzEgLTIweiIvPgo8cGF0aCBkPSJNNDM1IDI1NDUgYy0yOSAtMjggLTMxIC00NSAtMTAgLTg1IDMwIC01OCAxMTUgLTMxIDExNSAzNiAwIDM1IC04CjQ3IC0zNyA2MyAtMzEgMTYgLTQxIDE0IC02OCAtMTR6Ii8+CjxwYXRoIGQ9Ik00MzAgMjMxMSBjLTEyIC0yMyAtMTMgLTYxIC0yIC03NyAxOCAtMjggNTYgLTM0IDIwNyAtMzQgMTg4IDAgMjE1CjggMjE1IDY4IDAgMjEgLTUgNDMgLTEyIDUwIC05IDkgLTY4IDEyIC0yMDUgMTIgLTE4MCAwIC0xOTMgLTEgLTIwMyAtMTl6Ii8+CjxwYXRoIGQ9Ik0yNzIgMjExOCBjLTcgLTcgLTEyIC0yOSAtMTIgLTUxIDAgLTU2IDMzIC02NyAyMDAgLTY3IDE0MCAwIDE4MyAxMQoxOTUgNTAgNyAyMyAtMyA2MiAtMTkgNzIgLTIwIDEzIC0zNTEgOSAtMzY0IC00eiIvPgo8cGF0aCBkPSJNNDMwIDE5MTEgYy0xNyAtMzIgLTEyIC03NiAxMSAtOTcgMTggLTE2IDQwIC0xOSAxNTcgLTIxIDEyNyAtMiAxMzkKLTEgMTY5IDIwIDM5IDI2IDQzIDYyIDEzIDk1IC0yMCAyMSAtMjkgMjIgLTE4MCAyMiAtMTQ3IDAgLTE2MCAtMSAtMTcwIC0xOXoiLz4KPHBhdGggZD0iTTQxMSAxNzA0IGMtMjggLTM1IC0yNiAtNTAgNSAtNzkgMzEgLTI5IDQ0IC0zMSA4NCAtMTAgMjQgMTMgMzAgMjIKMzAgNTAgMCA0MyAtMjIgNjUgLTY1IDY1IC0yNCAwIC0zOSAtNyAtNTQgLTI2eiIvPgo8L2c+Cjwvc3ZnPg==';
        add_menu_page(
            'Hyper PWA',
            'Hyper PWA',
            'manage_options',
            'hypwa-settings',
            [ $this, 'hypwa_render_settings_page' ],
            $menu_icon,
            60
        );
    }

    public function hypwa_enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_hypwa-settings' !== $hook ) {
            return;
        }
        
        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        wp_enqueue_style( 
            'hypwa-select2-styles', 
            HYPWA_DIR_URI . "assets/admin/css/external/select2.min.css", 
            [], 
            HYPWA_VERSION 
        );

        wp_enqueue_style(
            'hypwa-admin-styles',
            HYPWA_DIR_URI . "assets/admin/css/settings{$min}.css",
            ['hypwa-select2-styles'],   // ← depends on select2 CSS, loads after
            HYPWA_VERSION
        );
        wp_style_add_data( 'hypwa-admin-styles', 'rtl', true );

        wp_add_inline_style( 'wp-admin', '
            #wpcontent { padding-left: 0 !important; background: #f8fafc !important; }
            .notice, #wpfooter { display: none !important; }
            #wpbody-content { padding-bottom: 0 !important; }
        ' );

        wp_enqueue_script( 
            'hypwa-select2-scripts', 
            HYPWA_DIR_URI . "assets/admin/js/external/select2.min.js", 
            ['jquery'], 
            HYPWA_VERSION,
            true
        );

        // Enqueue Exterior JavaScript File
        wp_enqueue_script( 
            'hypwa-admin-scripts', 
            HYPWA_DIR_URI . "assets/admin/js/settings{$min}.js", 
            ['jquery', 'hypwa-select2-scripts'], 
            HYPWA_VERSION, 
            true 
        );

        wp_localize_script( 'hypwa-admin-scripts', 'hypwa_settings_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonces'   => [
                'save'   => wp_create_nonce( 'hypwa_save_settings_nonce' ),
                'reset'  => wp_create_nonce( 'hypwa_reset_settings_nonce' ),
                'export' => wp_create_nonce( 'hypwa_export_settings_nonce' ),
                'import' => wp_create_nonce( 'hypwa_import_settings_nonce' ),
                'migrate' => wp_create_nonce( 'hypwa_migration_nonce' ),
            ],
        ]);

    }

    public function hypwa_dequeue_conflicting_scripts( $hook ) {
        if ( 'toplevel_page_hypwa-settings' !== $hook ) {
            return;
        }

        wp_dequeue_script( 'select2-js' );
        wp_dequeue_script( 'select2' );
        wp_deregister_script( 'select2' );

        // Jupiter theme conflict
        wp_dequeue_script( 'mk-select2' );
        wp_deregister_script( 'mk-select2' );

        wp_dequeue_script( 'wds-shared-ui' );
        wp_deregister_script( 'wds-shared-ui' );

        wp_dequeue_script( 'pum-admin-general' );
        wp_deregister_script( 'pum-admin-general' );

        wp_dequeue_script( 'cmb-select2' );
        wp_deregister_script( 'cmb-select2' );
    }

    public function hypwa_render_settings_page() {
        $hypwa_options = get_option( 'hypwa_options', [] );
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
        $hypwa_current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'app_basics';
        $hypwa_base_url = admin_url( 'admin.php?page=hypwa-settings' );

        $hypwa_show_license_notice = hypwa_is_pro_license_inactive_notice_needed();
        
        ?>

        <div class="hypwa-app-wrapper">

            <?php if ( ! hypwa_is_pro_plugin_active() ) : ?>
                <div class="hypwa-edd-discount-bar">
                    <span class="hypwa-edd-offer-badge"><?php esc_html_e( '🔥 Offer', 'hyper-pwa' ); ?></span>
                    <span class="hypwa-edd-offer-text">
                        <?php esc_html_e( 'Get', 'hyper-pwa' ); ?> <strong><?php esc_html_e( '40% OFF', 'hyper-pwa' ); ?></strong> <?php esc_html_e( 'Agency & Lifetime plans (Code:', 'hyper-pwa' ); ?> <strong class="hypwa-edd-offer-code"><?php esc_html_e( 'HYPER40', 'hyper-pwa' ); ?></strong>) <?php esc_html_e( 'or', 'hyper-pwa' ); ?> <strong><?php esc_html_e( '10% OFF', 'hyper-pwa' ); ?></strong> <?php esc_html_e( 'other plans (Code:', 'hyper-pwa' ); ?> <strong class="hypwa-edd-offer-code"><?php esc_html_e( 'HYPER10', 'hyper-pwa' ); ?></strong>)!
                    </span>
                    <a href="https://hyperpwa.com/premium/#pricing" target="_blank" class="hypwa-edd-shop-btn">
                        <?php esc_html_e( 'Shop Now ➔', 'hyper-pwa' ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="hypwa-main-container">
                
                <header class="hypwa-top-header">
                    <div class="hypwa-brand-inline">                        
                        <img src="<?php echo esc_url( HYPWA_DIR_URI . 'assets/admin/img/icon-100x100.png' ); ?>" alt="Hyper PWA Logo" width="70" height="70">
                        <div class="hypwa-brand-version"><?php echo esc_html( HYPWA_VERSION ); ?></div>
                    </div>
                    <div class="hypwa-header-actions">
                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" class="hypwa-header-link"><span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Documentation', 'hyper-pwa' );?></a>
                        <a target="_blank" href="https://hyperpwa.com/contactus/" class="hypwa-header-link"><span class="dashicons dashicons-businessman"></span> <?php esc_html_e( 'Support', 'hyper-pwa' );?></a>                        
                    </div>
                </header>

                <?php if ( $hypwa_show_license_notice ) : ?>
                    <div class="hypwa-fullwidth-notice-row">
                        <div class="hypwa-notice hypwa-notice-warning">
                            <span class="dashicons dashicons-warning hypwa-notice-icon"></span>
                            <div class="hypwa-notice-text">
                                <strong><?php esc_html_e( 'Activate Your License', 'hyper-pwa' ); ?></strong>
                                <span class="hypwa-notice-sep">&mdash;</span>
                                <span class="hypwa-notice-desc"><?php esc_html_e( "Hyper PWA Premium is installed, but your license key isn't activated yet. Activate it to unlock all premium features.", 'hyper-pwa' ); ?></span>
                            </div>
                            <a href="<?php echo esc_url( add_query_arg( 'tab', 'license', $hypwa_base_url ) ); ?>" class="hypwa-notice-btn">
                                <?php esc_html_e( 'Activate License', 'hyper-pwa' ); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" id="hypwa-settings-form">
                    <?php settings_fields( 'hypwa_settings_group' ); ?>
                    
                    <div class="hypwa-page-body-grid">
                        
                        <div class="hypwa-settings-pane">                                                        
                            
                            <?php
                                $hypwa_tabs = apply_filters(
                                    'hypwa_settings_tabs',
                                    [
                                        'app_basics'      => esc_html__( 'App Basics', 'hyper-pwa' ),
                                        'core_features'   => esc_html__( 'Core Features', 'hyper-pwa' ),
                                        'premium_features'=> esc_html__( 'Premium Features', 'hyper-pwa' ),
                                        'advanced'        => esc_html__( 'Advanced', 'hyper-pwa' ),
                                        'tools'           => esc_html__( 'Tools', 'hyper-pwa' ),
                                        'compatibility'   => esc_html__( 'Compatibility', 'hyper-pwa' ),
                                        'migration'       => esc_html__( 'Migration', 'hyper-pwa' ),                                        
                                        'support'         => esc_html__( 'Support', 'hyper-pwa' ),
                                    ]
                                );
                                ?>

                                <div class="hypwa-underline-tabs">
                                    <?php foreach ( $hypwa_tabs as $tab_key => $tab_label ) : ?>
                                        <?php
                                        $tab_url = 'app_basics' === $tab_key
                                            ? $hypwa_base_url
                                            : add_query_arg( 'tab', $tab_key, $hypwa_base_url );
                                        ?>
                                        <a href="<?php echo esc_url( $tab_url ); ?>"
                                            class="hypwa-tab-link <?php echo $hypwa_current_tab === $tab_key ? 'active' : ''; ?>">
                                            <?php echo esc_html( $tab_label ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                            <?php  if ( $hypwa_current_tab === 'app_basics' ) : ?>
                                <div class="hypwa-standalone-content">
                                    <section class="hypwa-html-section">
                                        <?php
                                        require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-app-basic-settings.php';
                                        HYPWA_App_Basic_Settings::render_all_fields();
                                        ?>

                                    </section>
                                </div>
                            <?php elseif ( $hypwa_current_tab === 'advanced' ) : ?>

                            <div class="hypwa-standalone-content">
                                    <section class="hypwa-html-section">
                                        <?php
                                        require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-advanced-settings.php';
                                        HYPWA_Advanced_Settings::render_all_fields();
                                        ?>

                                    </section>
                            </div>

                            <?php elseif ( $hypwa_current_tab === 'core_features' ) : ?>

                                <div class="hypwa-accordion-stack">
                                    <?php
                                    require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-core-feature-settings.php';
                                    HYPWA_Core_Feature_Settings::render();
                                    ?>
                                </div>

                            <?php elseif ( $hypwa_current_tab === 'premium_features' ) : ?>
                                <div class="hypwa-accordion-stack">
                                    <?php
                                    require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-premium-features-settings.php';
                                    HYPWA_Premium_Feature_Settings::render();
                                    ?>
                                </div>
                                <?php elseif ( $hypwa_current_tab === 'tools' ) : ?>
                                <div class="hypwa-standalone-content hypwa-tools-scope">
                                    <div class="hypwa-html-section">
                                    <?php
                                    require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-tools-settings.php';
                                    HYPWA_Tools_Settings::render();
                                    ?>        
                                    </div>
                                </div>

                                <?php elseif ( $hypwa_current_tab === 'compatibility' ) : ?>
                                <div class="hypwa-standalone-content">
                                    <section class="hypwa-html-section">
                                        <?php
                                        require_once HYPWA_PLUGIN_DIR_PATH .'includes/admin/settings/class-hypwa-compatibility-settings.php';
                                        HYPWA_Compatibility_Settings::render();
                                        ?>  
                                    </section>  
                                </div>
                                <?php elseif ( $hypwa_current_tab === 'migration' ) : ?>
                                    <div class="hypwa-standalone-content" >
                                        <div class="hypwa-html-section">
                                            <div class="hypwa-form-row">
                                                <div class="hypwa-label-col">
                                                    <label><?php echo esc_html__( 'Migrate from SuperPWA', 'hyper-pwa' );?></label>
                                                    <span class="hypwa-field-desc"><?php echo esc_html__( ' Seamlessly transfer your SuperPWA settings, design preferences, and configuration data to Hyper PWA without manual setup.', 'hyper-pwa' );?>
                                                        <a href="https://hyperpwa.com/knowledge-base/how-to-migrate-from-superpwa-to-hyper-pwa-without-losing-your-settings/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link" style="margin-left: 5px;">
                                                            <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                                                            <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="hypwa-input-col">
                                                    <button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide hypwa-migrte-loading-btn" disabled>
                                                        <span class="hypwa-spinner"></span>
                                                        <span class="hypwa-btn-text"><?php echo esc_html__( 'Migrating...','hyper-pwa' );?></span>
                                                    </button>
                                                    <button type="button" class="hypwa-btn-blue hypwa-pwa-migration-btn" data-plugin="super-pwa" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <?php echo esc_html__( 'Start Migration', 'hyper-pwa' ); ?>
                                                        <!-- Right-facing directional arrow icon placed after text -->
                                                        <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span>
                                                    </button>
                                                    <div class="hypwa-field-message hypwa-field-message-success hypwa-hide" id="hypwa-super-pwa-success-msg">
                                                        <span class="dashicons dashicons-yes-alt"></span>
                                                    </div>
                                                    <div class="hypwa-field-message hypwa-field-message-error hypwa-hide" id="hypwa-super-pwa-error-msg">
                                                        <span class="dashicons dashicons-warning"></span>
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="hypwa-form-row">
                                                <div class="hypwa-label-col">
                                                    <label><?php echo esc_html__( 'Migrate from PWAforWP', 'hyper-pwa' );?></label>
                                                    <span class="hypwa-field-desc"><?php echo esc_html__( 'Seamlessly transfer your PWAforWP settings, design preferences, and configuration data to Hyper PWA without manual setup.', 'hyper-pwa' ); ?>
                                                        <a href="https://hyperpwa.com/knowledge-base/how-to-migrate-from-pwa-for-wp-to-hyper-pwa-without-losing-your-settings/" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link" style="margin-left: 5px;">
                                                            <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                                                            <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="hypwa-input-col">
                                                    <button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide hypwa-migrte-loading-btn" disabled>
                                                        <span class="hypwa-spinner"></span>
                                                        <span class="hypwa-btn-text"><?php echo esc_html__( 'Migrating...','hyper-pwa' );?></span>
                                                    </button>
                                                    <button type="button" class="hypwa-btn-blue hypwa-pwa-migration-btn" data-plugin="pwa" style="display: inline-flex; align-items: center; gap: 8px;">
                                                        <?php echo esc_html__( 'Start Migration', 'hyper-pwa' );?>
                                                        <!-- Right-facing directional arrow icon placed after text -->
                                                        <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; line-height: 16px;"></span>
                                                    </button>
                                                    <div class="hypwa-field-message hypwa-field-message-success hypwa-hide" id="hypwa-pwa-success-msg">
                                                        <span class="dashicons dashicons-yes-alt"></span>
                                                    </div>
                                                    <div class="hypwa-field-message hypwa-field-message-error hypwa-hide" id="hypwa-pwa-eerror-msg">
                                                        <span class="dashicons dashicons-warning"></span>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>       
                                    </div>                   

                            <?php elseif ( $hypwa_current_tab === 'support' ) : ?>
    
                                <div class="hypwa-standalone-content">
                                    <div class="hypwa-html-section">                                                                                            

                                        <div class="hypwa-form-row" style="display: flex; flex-wrap: wrap; align-items: stretch; gap: 24px;">
                                            
                                            <div style="flex: 1; min-width: 380px;">
                                                                                                            
                                        <div class="hypwa-form-row" style="flex-direction: column; align-items: stretch; gap: 8px;">
                                            <div class="hypwa-label-col" style="width: 100%;">
                                                <label for="detailed_description"><?php esc_html_e( 'Ticket Subject', 'hyper-pwa' ); ?></label>
                                            </div>                        
                                                <input type="text" id="ticket_subject" name="ticket_subject" class="hypwa-text-input" placeholder="<?php esc_attr_e( 'Issue Subject', 'hyper-pwa' ); ?>">                        
                                        </div>

                                        <div class="hypwa-form-row" style="flex-direction: column; align-items: stretch; gap: 8px;">
                                            <div class="hypwa-label-col" style="width: 100%;">
                                                <label for="detailed_description"><?php esc_html_e( 'Your Email', 'hyper-pwa' ); ?></label>
                                            </div>                        
                                                <input type="email" id="ticket_email" name="ticket_email" class="hypwa-text-input" placeholder="<?php esc_attr_e( 'Your Email', 'hyper-pwa' ); ?>">                        
                                        </div>
                                        
                                        <div class="hypwa-form-row" style="flex-direction: column; align-items: stretch; gap: 8px;">
                                            <div class="hypwa-label-col" style="width: 100%;">
                                                <label for="detailed_description"><?php esc_html_e( 'Detailed Description', 'hyper-pwa' ); ?></label>
                                            </div>
                                            <div class="hypwa-input-col" style="width: 100%;">                           
                                                <textarea id="detailed_description" name="detailed_description" class="hypwa-textarea-input" style="height: 120px;" placeholder="<?php esc_attr_e( 'Describe your issue...', 'hyper-pwa' ); ?>"></textarea>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 20px; display:flex; align-items:center; justify-content:flex-end; gap:12px;">

                                            <div class="hypwa-ticket-message"></div>

                                            <button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide" disabled>
                                                <span class="hypwa-spinner"></span>
                                                <span class="hypwa-btn-text"><?php esc_html_e( 'Sending...', 'hyper-pwa' ); ?></span>
                                            </button>

                                            <button type="button" class="hypwa-btn-blue hypwa-support">
                                                <?php esc_html_e( 'Submit Ticket', 'hyper-pwa' ); ?>
                                            </button>

                                        </div>
                                    
                                            </div>

                                            <div style="flex: 0 0 40%; max-width: 40%; min-width: 200px; justify-content: center;">
                                                                      <div class="hypwa-side-widget">
                                                    <div class="hypwa-widget-title">
                                                        <span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Quick Knowledge Base', 'hyper-pwa' ); ?>
                                                    </div>
                                                    <ul class="hypwa-side-links-list">
                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-installation-guide-btn">
                                                            <span><span class="dashicons dashicons-welcome-learn-more"></span><?php esc_html_e( 'Installation Guide', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-app-basics-kb-btn">
                                                            <span><span class="dashicons dashicons-book"></span><?php esc_html_e( 'App Basics Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-core-features-kb-btn">
                                                            <span><span class="dashicons dashicons-media-document"></span><?php esc_html_e( 'Core Features Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                        <input type="file" id="hypwa-import-file" accept=".json" style="display:none;">
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-premium-features-kb-btn">
                                                            <span><span class="dashicons dashicons-awards"></span><?php esc_html_e( 'Premium Features Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-tools-kb-btn">
                                                            <span><span class="dashicons dashicons-editor-help"></span><?php esc_html_e( 'Tools Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-compatibility-kb-btn">
                                                            <span><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Compatibility Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a target="_blank" href="https://hyperpwa.com/knowledge-base/" id="hypwa-migration-kb-btn">
                                                            <span><span class="dashicons dashicons-migrate"></span><?php esc_html_e( 'Migration Knowledge Base', 'hyper-pwa' ); ?></span>
                                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                                </div>
                                            </div>

                                        </div>                                    
                                    </div>
                                </div>
                            <?php else : ?>
                                <?php
                                    do_action( 'hypwa_settings_tabs_content_' . $hypwa_current_tab );
                                ?>                                
                            <?php endif; ?>
                        </div>

                        <aside class="hypwa-right-sidebar-pane">
                            
                            <div class="hypwa-side-widget">
                                <?php
                                    $issues_count = 0;
                                    $is_https = hypwa_is_https();
                                    $is_manifest_valid = hypwa_is_manifest_valid();
                                    $is_sw_active = hypwa_is_service_worker_active();
                                    $has_app_icon = hypwa_has_app_icon();
                                    $has_offline_page = hypwa_has_offline_page();

                                    if ( ! $is_https ) $issues_count++;
                                    if ( ! $is_manifest_valid ) $issues_count++;
                                    if ( ! $is_sw_active ) $issues_count++;
                                    if ( ! $has_app_icon ) $issues_count++;
                                    if ( ! $has_offline_page ) $issues_count++;

                                    $bubble_bg = $issues_count > 0 ? '#fef2f2' : '#f0fdf4';
                                    $bubble_color = $issues_count > 0 ? '#dc2626' : '#16a34a';
                                    $bubble_text = $issues_count === 1 ? esc_html__( '1 Issue', 'hyper-pwa' ) : sprintf( esc_html__( '%d Issues', 'hyper-pwa' ), $issues_count );
                                ?>
                                <div class="hypwa-widget-title" style="justify-content: space-between; margin-bottom: 4px;">
                                    <div>
                                        <span class="dashicons dashicons-heart blue-txt"></span> <?php esc_html_e( 'PWA Health', 'hyper-pwa' ); ?>
                                    </div>
                                    <span style="background-color: <?php echo esc_attr( $bubble_bg ); ?>; color: <?php echo esc_attr( $bubble_color ); ?>; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html( $bubble_text ); ?></span>
                                </div>
                                
                                <p class="hypwa-widget-desc"><?php esc_html_e( 'Ensure your PWA is properly configured and working as expected.', 'hyper-pwa' ); ?></p>

                                <ul class="hypwa-side-links-list">
                                    <li>
                                        <a href="#">
                                            <span>
                                                <span class="dashicons <?php echo esc_attr( $is_https ? 'dashicons-yes-alt' : 'dashicons-dismiss' ); ?>" style="color: <?php echo esc_attr( $is_https ? '#16a34a' : '#dc2626' ); ?>;"></span>
                                                <?php esc_html_e( 'HTTPS', 'hyper-pwa' ); ?>
                                            </span>
                                            <span style="font-size: 11px; color: #64748b;">
                                                <?php echo esc_html( $is_https ? __( 'Enabled', 'hyper-pwa' ) : __( 'Disabled', 'hyper-pwa' ) ); ?>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=hypwa-settings' ) ); ?>">
                                            <span>
                                                <span class="dashicons <?php echo esc_attr( $is_manifest_valid ? 'dashicons-yes-alt' : 'dashicons-dismiss' ); ?>" style="color: <?php echo esc_attr( $is_manifest_valid ? '#16a34a' : '#dc2626' ); ?>;"></span>
                                                <?php esc_html_e( 'Manifest', 'hyper-pwa' ); ?>
                                            </span>
                                            <span style="font-size: 11px; color: <?php echo esc_attr( $is_manifest_valid ? '#64748b' : '#ef4444' ); ?>;<?php echo $is_manifest_valid ? '' : ' font-weight: 600;'; ?>">
                                                <?php echo esc_html( $is_manifest_valid ? __( 'Valid', 'hyper-pwa' ) : __( 'Invalid', 'hyper-pwa' ) ); ?>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=hypwa-settings' ) ); ?>">
                                            <span>
                                                <span class="dashicons <?php echo esc_attr( $is_sw_active ? 'dashicons-yes-alt' : 'dashicons-dismiss' ); ?>" style="color: <?php echo esc_attr( $is_sw_active ? '#16a34a' : '#dc2626' ); ?>;"></span>
                                                <?php esc_html_e( 'Service Worker', 'hyper-pwa' ); ?>
                                            </span>
                                            <span style="font-size: 11px; color: <?php echo esc_attr( $is_sw_active ? '#64748b' : '#ef4444' ); ?>;<?php echo $is_sw_active ? '' : ' font-weight: 600;'; ?>">
                                                <?php echo esc_html( $is_sw_active ? __( 'Active', 'hyper-pwa' ) : __( 'Missing', 'hyper-pwa' ) ); ?>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <?php $has_app_icon = hypwa_has_app_icon(); ?>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=hypwa-settings' ) ); ?>">
                                            <span>
                                                <span class="dashicons <?php echo esc_attr( $has_app_icon ? 'dashicons-yes-alt' : 'dashicons-warning' ); ?>" style="color: <?php echo esc_attr( $has_app_icon ? '#16a34a' : '#ef4444' ); ?>;"></span>
                                                <?php esc_html_e( 'App Icon', 'hyper-pwa' ); ?>
                                            </span>
                                            <span style="font-size: 11px; color: <?php echo esc_attr( $has_app_icon ? '#64748b' : '#ef4444' ); ?>;<?php echo $has_app_icon ? '' : ' font-weight: 600;'; ?>">
                                                <?php echo esc_html( $has_app_icon ? __( 'Configured', 'hyper-pwa' ) : __( 'Missing', 'hyper-pwa' ) ); ?>
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <?php $has_offline_page = hypwa_has_offline_page(); ?>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=hypwa-settings' ) ); ?>">
                                            <span>
                                                <span class="dashicons <?php echo esc_attr( $has_offline_page ? 'dashicons-yes-alt' : 'dashicons-warning' ); ?>" style="color: <?php echo esc_attr( $has_offline_page ? '#16a34a' : '#ef4444' ); ?>;"></span>
                                                <?php esc_html_e( 'Offline Page', 'hyper-pwa' ); ?>
                                            </span>
                                            <span style="font-size: 11px; color: <?php echo esc_attr( $has_offline_page ? '#64748b' : '#ef4444' ); ?>;<?php echo $has_offline_page ? '' : ' font-weight: 600;'; ?>">
                                                <?php echo esc_html( $has_offline_page ? __( 'Configured', 'hyper-pwa' ) : __( 'Missing', 'hyper-pwa' ) ); ?>
                                            </span>
                                        </a>
                                    </li>

                                </ul>

                            </div>

                            <div class="hypwa-side-widget">
                                <div class="hypwa-widget-title">
                                    <span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Quick Links', 'hyper-pwa' ); ?>
                                </div>
                                <?php
                                $migration_url = esc_url( admin_url( 'admin.php?page=hypwa-settings&tab=migration' ) );
                                ?>
                                <ul class="hypwa-side-links-list">
                                    <li>
                                        <a href="<?php echo esc_url( $migration_url ); ?>" id="hypwa-migrate-superpwa-btn">
                                            <span><span class="dashicons dashicons-database-export"></span> <?php esc_html_e( 'Migrate from SuperPWA', 'hyper-pwa' ); ?></span>
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="<?php echo esc_url( $migration_url ); ?>" id="hypwa-migrate-pwaforwp-btn">
                                            <span><span class="dashicons dashicons-database-import"></span> <?php esc_html_e( 'Migrate from PWAforWP', 'hyper-pwa' ); ?></span>
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </a>
                                        <input type="file" id="hypwa-import-file" accept=".json" style="display:none;">
                                    </li>

                                    <li>
                                        <?php
                                        $reset_url = esc_url( admin_url( 'admin.php?page=hypwa-settings&tab=tools' ) );
                                        ?>
                                        <a href="<?php echo esc_url( $reset_url ); ?>">
                                            <span><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Reset Settings', 'hyper-pwa' ); ?></span>
                                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="hypwa-side-widget hypwa-review-widget">
                                <div class="hypwa-widget-title">
                                    <span class="dashicons dashicons-heart"></span> <?php esc_html_e( 'Enjoying Hyper PWA?', 'hyper-pwa' ); ?>
                                </div>
                                <div class="hypwa-widget-desc">
                                    <?php esc_html_e( 'If you find our plugin helpful, please consider leaving a 5-star review. It helps us keep improving!', 'hyper-pwa' ); ?>
                                </div>
                                <ul class="hypwa-side-links-list">
                                    <li>
                                        <a href="https://wordpress.org/support/plugin/hyper-pwa/reviews/#new-post" target="_blank" id="hypwa-leave-review-btn">
                                            <span><span class="dashicons dashicons-star-filled" style="color: #ffb900;"></span> <?php esc_html_e( 'Leave a Review', 'hyper-pwa' ); ?></span>
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="https://hyperpwa.com/contactus/" target="_blank" id="hypwa-support-btn">
                                            <span><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Get Support', 'hyper-pwa' ); ?></span>
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </aside>

                    </div>
                    
                    <footer class="hypwa-bottom-bar">
                        <!-- Left Section: Premium Promo (70% Width) + Divider -->
                         <div class="hypwa-footer-left">
                        <?php if ( ! class_exists( 'HYPWAP_Initial_Loader' ) ) : ?>

                                <div class="hypwa-premium-banner">
                                    <span class="dashicons dashicons-admin-plugins hypwa-premium-icon"></span>

                                    <div class="hypwa-premium-text">
                                        <strong><?php esc_html_e( 'Upgrade to Hyper PWA Premium', 'hyper-pwa' ); ?></strong>
                                        <p><?php esc_html_e( 'Unlock all premium features like Offline Forms, Custom Install App, and priority support.', 'hyper-pwa' ); ?></p>
                                    </div>

                                    <a href="https://hyperpwa.com/premium/" class="hypwa-btn-premium-upgrade" target="_blank">
                                        <?php esc_html_e( 'Upgrade Now', 'hyper-pwa' ); ?>
                                    </a>
                                </div>

                            <?php else : ?>

                                <div class="hypwa-premium-banner hypwa-premium-active">
                                    <span class="dashicons dashicons-yes-alt hypwa-premium-icon hypwa-premium-active-icon"></span>

                                    <div class="hypwa-premium-text">
                                        <strong><?php esc_html_e( 'Hyper PWA Premium is Active', 'hyper-pwa' ); ?></strong>
                                        <p><?php esc_html_e( 'Thank you for supporting Hyper PWA! You have access to all premium features and future updates.', 'hyper-pwa' ); ?></p>
                                    </div>
                                </div>

                            <?php endif; ?>
                        </div>
                        <!-- Right Section: Actions & Save Button (30% Width) -->
                        <div class="hypwa-footer-right">
                            <div class="hypwa-footer-left">
                                <div class="hypwa-bottom-status"></div>
                            </div>

                            <div class="hypwa-footer-right" style="
                                width: 80%;
                                display: contents;
                            ">
                                <div class="hypwa-bottom-actions">
                                    <button class="hypwa-saving-btn hypwa-is-loading hypwa-btn-blue hypwa-hide" id="hypwa-save-changes-load-btn" disabled>
                                        <span class="hypwa-spinner"></span>
                                        <span class="hypwa-btn-text"><?php esc_html_e( 'Saving...', 'hyper-pwa' ); ?></span>
                                    </button>
                                    <button type="submit" name="submit" class="hypwa-btn-blue hypwa-save-changes-btn"><?php esc_html_e( 'Save Changes', 'hyper-pwa' ); ?></button>
                                </div>
                            </div>
                        </div>
                    </footer>

                </form>
            </div>
        </div>
        <?php
    }


    public static function render($type, $args) {
        $args = wp_parse_args($args, [
            'id'          => '',
            'class'       => '',
            'name'        => '',
            'value'       => '',
            'label'       => '',
            'desc'        => '',
            'placeholder' => '',
            'options'     => [],
            'min'         => 0,
            'max'         => 9999,
        ]);

        switch ($type) {
            case 'text':
            case 'email':
            case 'url':
            case 'password':
                self::render_text($args);
                break;

            case 'number':
                self::render_number($args);
                break;

            case 'textarea':
                self::render_textarea($args);
                break;

            case 'select':
                self::render_select($args);
                break;

            case 'multiselect':
                self::render_multiselect($args);
                break;

            case 'upload':
                self::render_upload($args);
                break;

            case 'checkbox':
                self::render_checkbox($args);
                break;

            case 'radio':
                self::render_radio($args);
                break;

            case 'toggle':
                self::render_toggle($args);
                break;

            case 'color':
                self::render_color($args);
                break;

            default:
                echo '<p style="color:red;">Unknown field type: ' . esc_html($type) . '</p>';
                break;
        }
    }

    public static function row_open($args, $for = '') {
        ?>
        <div class="hypwa-form-row">
            <div class="hypwa-label-col">
                <label <?php echo $for ? 'for="' . esc_attr($for) . '"' : ''; ?>>
                    <?php echo esc_html($args['label']); ?>
                </label>
                <span class="hypwa-field-desc"><?php echo wp_kses_post($args['desc']); ?></span>
            </div>
            <div class="hypwa-input-col">
        <?php
    }

    public static function row_close() {
        ?>
            </div>
        </div>
        <?php
    }

    // -----------------------------------------------
    // Field Renderers
    // -----------------------------------------------

    public static function render_text($args) {
        self::row_open($args, $args['id']);
        $class = isset( $args['class'] ) ? $args['class'] : ''; 
        ?>
        <input
            type="<?php echo esc_attr($args['type'] ?? 'text'); ?>"
            id="<?php echo esc_attr($args['id']); ?>"
            class="hypwa-text-input <?php echo esc_attr( $class ); ?>"
            name="<?php echo esc_attr($args['name']); ?>"
            value="<?php echo esc_attr($args['value']); ?>"
            placeholder="<?php echo esc_attr($args['placeholder']); ?>"
        />
        <?php
        self::row_close();
    }

    public static function render_number($args) {
        self::row_open($args, $args['id']);
        ?>
        <input
            type="number"
            id="<?php echo esc_attr($args['id']); ?>"
            class="hypwa-text-input"
            name="<?php echo esc_attr($args['name']); ?>"
            value="<?php echo esc_attr($args['value']); ?>"
            min="<?php echo esc_attr($args['min']); ?>"
            max="<?php echo esc_attr($args['max']); ?>"
        />
        <?php
        self::row_close();
    }

    public static function render_textarea($args) {
        self::row_open($args, $args['id']);
        ?>
        <textarea
            id="<?php echo esc_attr($args['id']); ?>"
            class="hypwa-textarea-input"
            name="<?php echo esc_attr($args['name']); ?>"
            placeholder="<?php echo esc_attr($args['placeholder']); ?>"
        ><?php echo esc_textarea($args['value']); ?></textarea>
        <?php
        self::row_close();
    }

    public static function render_select($args) {
        self::row_open($args, $args['id']);
        ?>
        <select
            id="<?php echo esc_attr($args['id']); ?>"
            name="<?php echo esc_attr($args['name']); ?>"
            class="<?php echo esc_attr( $args['class'] );?>"
        >
            <?php foreach ($args['options'] as $opt_value => $opt_label) : ?>
                <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($args['value'], $opt_value); ?>>
                    <?php echo esc_html($opt_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
        self::row_close();
    }

    public static function render_multiselect($args) {
        self::row_open($args);

        $raw_values = $args['value'] ?? [];
        
        // Ensure it's treated as an array and cast all elements to strings
        $selected_values = is_array($raw_values) ? $raw_values : [$raw_values];
        $selected_values = array_map('strval', $selected_values);

        $class_attr = ! empty($args['class']) ? esc_attr($args['class']) : 'hypwa-select2';
        $id_attr    = ! empty($args['id']) ? esc_attr($args['id']) : '';
        
        // Ensure the name attribute ends with [] for array submission
        $name_attr  = esc_attr($args['name'] ?? '');
        if (substr($name_attr, -2) !== '[]') {
            $name_attr .= '[]';
        }
        ?>
        
        <input 
            type="hidden" 
            name="<?php echo esc_attr( $args['name'] ); ?>" 
            value="" 
        />

        <select 
            id="<?php echo esc_attr( $id_attr ); ?>" 
            name="<?php echo esc_attr( $name_attr ); ?>" 
            class="<?php echo esc_attr( $class_attr ); ?>" 
            multiple="multiple" 
            style="width: 100% !important;"
        >
            <option></option> 

            <?php if ( ! empty($args['options']) && is_array($args['options']) ) : ?>
                <?php foreach ( $args['options'] as $opt_value => $opt_label ) : 
                    $is_selected = in_array((string)$opt_value, $selected_values, true); 
                    ?>
                    <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($is_selected, true); ?>>
                        <?php echo esc_html($opt_label); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <?php
        self::row_close();
    }

    public static function render_upload($args) {
        self::row_open($args, $args['id']);
        ?>
        <div class="hypwa-upload-wrapper">
            <input
                type="text"
                id="<?php echo esc_attr($args['id']); ?>"
                class="hypwa-text-input"
                name="<?php echo esc_attr($args['name']); ?>"
                value="<?php echo esc_attr($args['value']); ?>"
                placeholder="https://example.com/wp-content/uploads/image.jpg"
            />
            <button type="button" class="hypwa-upload-btn hypwa-widget-btn-outline" data-target="<?php echo esc_attr($args['id']); ?>">
                <span class="dashicons dashicons-upload"></span> Media Upload
            </button>
        </div>
        <?php
        self::row_close();
    }

    public static function render_checkbox($args) {
        self::row_open($args);

        // Determine checked state based on single value comparison (handles '1', 1, 'on', or true)
        $is_checked = ! empty($args['value']) && ($args['value'] === '1' || $args['value'] === 1 || $args['value'] === 'on' || $args['value'] === true);
        
        // Fallback label text if none is explicitly declared
        $label_text = isset($args['label']) ? $args['label'] : '';
        ?>
        <div class="hypwa-controls-vertical-list">
            <div class="hypwa-toggle-label-wrap">
                <label class="hypwa-switch">
                    <input
                        type="hidden"
                        name="<?php echo esc_attr($args['name']); ?>"
                        value="0"
                    />
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($args['id']); ?>"
                        name="<?php echo esc_attr($args['name']); ?>"
                        value="1"
                        <?php checked($is_checked, true); ?>
                    />
                    <span class="hypwa-slider hypwa-option-slider"></span>
                </label>
                <span class="hypwa-toggle-txt"><?php echo $is_checked ? 'ON' : 'OFF'; ?></span>
            </div>
        </div>
        <?php
        self::row_close();
    }

    private static function render_radio($args) {
        self::row_open($args);
        ?>
        <div class="hypwa-controls-vertical-list">
            <?php foreach ($args['options'] as $opt_value => $opt_label) : ?>
                <label class="hypwa-html-control-lbl">
                    <input
                        type="radio"
                        name="<?php echo esc_attr($args['name']); ?>"
                        value="<?php echo esc_attr($opt_value); ?>"
                        <?php checked($args['value'], $opt_value); ?>
                    /> <?php echo esc_html($opt_label); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
        self::row_close();
    }

    public static function render_toggle($args) {
        self::row_open($args, $args['id']);
        $checked = !empty($args['value']) ? 'checked' : '';
        $class = isset( $args['class'] ) ? $args['class'] : ''; 
        ?>
        <div class="hypwa-toggle-label-wrap">
            <label class="hypwa-switch">
                <input
                    type="checkbox"
                    id="<?php echo esc_attr($args['id']); ?>"
                    name="<?php echo esc_attr($args['name']); ?>"
                    value="1"
                    <?php echo esc_attr( $checked ); ?>
                />
                <span class="hypwa-slider hypwa-option-slider"></span>
            </label>
            <span class="hypwa-toggle-txt"><?php echo $checked ? 'ON' : 'OFF'; ?></span>
        </div>
        <?php
        self::row_close();
    }

    private static function render_color($args) {
        self::row_open($args, $args['id']);
        ?>
        <input
            type="text"
            id="<?php echo esc_attr($args['id']); ?>"
            class="hypwa-color-input"
            name="<?php echo esc_attr($args['name']); ?>"
            value="<?php echo esc_attr($args['value']); ?>"
        />

        <?php
        self::row_close();

    }

    public function render_learnmore( $link = '' ) {
        
        ?>
        <div class="hypwa-doc-link-footer">
            <span class="dashicons dashicons-book-alt"></span>
            <?php esc_html_e( 'Need help with this feature?', 'hyper-pwa' ); ?>
            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer" class="hypwa-doc-link">
                <?php esc_html_e( 'Learn more', 'hyper-pwa' ); ?>
                <span class="dashicons dashicons-external"></span>
            </a>
        </div>
        <?php

    }


}

new HYPWA_Settings();