<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'hypwa_register_rewrite_rules' );
add_action( 'parse_request', 'hypwa_serve_dynamic_assets' );

add_action( 'wp_head', 'hypwa_output_manifest_tags', 0 );
add_action( 'wp_enqueue_scripts', 'hypwa_enqueue_service_worker' );

/**
 * Output manifest tags.
 *
 * @return void
 */
function hypwa_output_manifest_tags() {
$manifest_url = hypwa_manifest_url();
?>
<!-- Hyper PWA Manifest -->
<link rel="manifest" href="<?php echo esc_url( $manifest_url ); ?>">
<?php if ( HYPWA_Options::get( 'preload_app_manifiest' ) ) : ?>
<link rel="prefetch" href="<?php echo esc_url( $manifest_url ); ?>">
<?php endif; ?>

<!-- iOS / Safari Compatibility Meta Tags -->
<?php if ( '1' === HYPWA_Options::get( 'ios_prompt_status', '0' ) ) : ?>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( HYPWA_Options::get( 'apple_status_bar_style', 'default' ) ); ?>">
<meta name="apple-mobile-web-app-title" content="<?php echo esc_attr( HYPWA_Options::get( 'app_short_name' ) ); ?>">

<?php
$apple_icon = HYPWA_Options::get( 'apple_touch_icon' );
if ( empty( $apple_icon ) ) {
	$apple_icon = HYPWA_Options::get( 'app_icon' );
}
if ( ! empty( $apple_icon ) ) : ?>
<link rel="apple-touch-icon" href="<?php echo esc_url( $apple_icon ); ?>">
<?php endif; ?>

<?php
// iOS Splash Screens
if ( '1' === HYPWA_Options::get( 'ios_splash_screens_enabled', '0' ) ) {
	$ios_devices = hypwa_get_ios_devices();

	foreach ( $ios_devices as $device ) {
		$splash_w = $device['w'] * $device['dpr'];
		$splash_h = $device['h'] * $device['dpr'];
		$url = add_query_arg(
			[
				'hypwa_ios_splash' => 1,
				'w'                => $splash_w,
				'h'                => $splash_h,
			],
			home_url( '/' )
		);
		?>
<link rel="apple-touch-startup-image" href="<?php echo esc_url( $url ); ?>" media="<?php echo esc_attr( $device['media'] ); ?>">
		<?php
	}
}
endif; ?>

<meta name="theme-color" content="<?php echo esc_attr( HYPWA_Options::get( 'theme_color', '#2563eb' ) ); ?>">
<?php
}

/**
 * Enqueue service worker registration script.
 *
 * @return void
 */
function hypwa_enqueue_service_worker() {
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_script(
		'hypwa-register-sw',
		HYPWA_PLUGIN_URL . "assets/customer/js/register-sw{$min}.js",
		[],
		HYPWA_VERSION,
		true
	);
		
	$script_data = [
		'sw_url'                      => hypwa_sw_url(),
		'scope'                       => trailingslashit( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/' ),
		'connectivity_notices_status' => HYPWA_Options::get( 'cf_connectivity_notices_status' ),
		'custom_install_app_status' => HYPWA_Options::get( 'pf_custom_install_app_status' ),
		'conn_notice_title'           => HYPWA_Options::get( 'cf_conn_notice_title' ),
		'conn_notice_description'     => HYPWA_Options::get( 'cf_conn_notice_description' ),
		'conn_notice_bg_color'        => HYPWA_Options::get( 'cf_conn_notice_bg_color' ),
		'conn_notice_text_color'      => HYPWA_Options::get( 'cf_conn_notice_text_color' ),
		'conn_notice_icon'            => HYPWA_Options::get( 'cf_conn_notice_icon', 'dashicons-wifi' ),
		'conn_online_notice_title'           => HYPWA_Options::get( 'cf_conn_online_notice_title' ),
		'conn_online_notice_description'     => HYPWA_Options::get( 'cf_conn_online_notice_description' ),
		'conn_online_notice_bg_color'        => HYPWA_Options::get( 'cf_conn_online_notice_bg_color' ),
		'conn_online_notice_text_color'      => HYPWA_Options::get( 'cf_conn_online_notice_text_color' ),
		'conn_online_notice_icon'            => HYPWA_Options::get( 'cf_conn_online_notice_icon', 'dashicons-wifi' ),
		'custom_install_trigger'      => HYPWA_Options::get( 'custom_install_trigger', '' ),
		'install_unsupported_msg'     => esc_html__( 'Installation is not supported on this browser/device, or the app is already installed.', 'hyper-pwa' ),
	];

	$script_data 	=	apply_filters( 'hypwa_service_worker_localize_data', $script_data );

	wp_localize_script(
		'hypwa-register-sw',
		'hypwa_sw',
		$script_data
	);

	wp_enqueue_script( 'hypwa-register-sw' );

	if ( '1' === HYPWA_Options::get( 'cf_push_status' ) && '1' === HYPWA_Options::get( 'cf_push_connected' ) && ! class_exists( 'HyperPushX' ) ) {
		wp_register_script(
			'hypwa-push-sdk',
			HYPWA_PLUGIN_URL . "assets/customer/js/hypwa-push-sdk{$min}.js",
			[ 'hypwa-register-sw' ],
			HYPWA_VERSION,
			true
		);

		$push_config = [
			'site_id'     => HYPWA_Options::get( 'cf_push_website_id' ),
			'backend_url' => HYPWA_PUSH_API_BASE,
		];

		wp_localize_script( 'hypwa-push-sdk', 'hypwa_push_config', $push_config );
		wp_enqueue_script( 'hypwa-push-sdk' );
	}

	if ( HYPWA_Options::get( 'cf_connectivity_notices_status' ) ) {

		wp_register_script(
			'hypwa-connectivity',
			HYPWA_PLUGIN_URL . "assets/customer/js/connectivity{$min}.js",
			[ 'hypwa-register-sw' ],
			HYPWA_VERSION,
			true
		);

		wp_enqueue_script( 'hypwa-connectivity' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style(
			'hypwa-connectivity',
			HYPWA_PLUGIN_URL . "assets/customer/css/connectivity{$min}.css",
			[],
			HYPWA_VERSION
		);
		wp_style_add_data( 'hypwa-connectivity', 'rtl', true );
	}

	if ( '1' === HYPWA_Options::get( 'ios_prompt_status', '0' ) ) {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script(
			'hypwa-ios-prompt',
			HYPWA_PLUGIN_URL . "assets/customer/js/ios-prompt{$min}.js",
			[],
			HYPWA_VERSION,
			true
		);

		$apple_icon = HYPWA_Options::get( 'apple_touch_icon' );
		if ( empty( $apple_icon ) ) {
			$apple_icon = HYPWA_Options::get( 'app_icon' );
		}

		wp_localize_script(
			'hypwa-ios-prompt',
			'hypwa_ios_prompt',
			[
				'app_name' => HYPWA_Options::get( 'app_short_name' ),
				'app_icon' => esc_url( $apple_icon ),
				'title'    => HYPWA_Options::get( 'ios_prompt_title' ) ?: 'Add to Home Screen',
				'desc'     => HYPWA_Options::get( 'ios_prompt_desc' ) ?: 'Install this app on your device for offline support and quick access.',
				'step1'    => HYPWA_Options::get( 'ios_prompt_step1' ) ?: 'Tap the Share button [share_icon] in the browser toolbar.',
				'step2'    => HYPWA_Options::get( 'ios_prompt_step2' ) ?: 'Scroll down and select Add to Home Screen.',
			]
		);

		wp_enqueue_script( 'hypwa-ios-prompt' );
		wp_enqueue_style(
			'hypwa-ios-prompt',
			HYPWA_PLUGIN_URL . "assets/customer/css/ios-prompt{$min}.css",
			[],
			HYPWA_VERSION
		);
		wp_style_add_data( 'hypwa-ios-prompt', 'rtl', true );
	}

	do_action( 'hypwa_enqueue_scripts_after_register_sw' );
}

/**
 * Register rewrite rules.
 *
 * @return void
 */
function hypwa_register_rewrite_rules() {

	if ( 'dynamic' === HYPWA_Options::get( 'file_serving_method', 'dynamic' ) ) {
		$sw_filename = hypwa_sw_filename();

		add_rewrite_rule(
			"^{$sw_filename}$",
			"index.php?{$sw_filename}=1",
			'top'
		);

		$m_filename = hypwa_manifest_filename();	

		add_rewrite_rule(
			"^{$m_filename}$",
			"index.php?{$m_filename}=1",
			'top'
		);
	}
}

function hypwa_serve_dynamic_assets( $wp ) {

	if ( isset( $_GET['hypwa_ios_splash'] ) ) {
		header( 'Content-Type: image/png' );
		require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/ios-splash-generator.php';
		hypwa_serve_ios_splash();
		exit;
	}

	if ( 'dynamic' === HYPWA_Options::get( 'file_serving_method', 'dynamic' ) ) {
		$request = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';

		if ( ( ! empty( $request ) && hypwa_manifest_filename() === $request ) || isset( $_GET['hypwa_manifest'] ) ) {

			header( 'Content-Type: application/manifest+json; charset=UTF-8' );

			echo wp_json_encode( hypwa_manifest_template() );
			exit;
		}

		if ( ( ! empty( $request ) && hypwa_sw_filename() === $request ) || isset( $_GET['hypwa_sw'] ) ) {

			header( 'Content-Type: application/javascript; charset=UTF-8' );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason: escaping is already hand;ed in this function hypwa_service_worker_template
			echo hypwa_service_worker_template();
			exit;
		}
	}
}

add_action( 'transition_post_status', 'hypwa_send_post_publish_push', 10, 3 );

function hypwa_send_post_publish_push( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}
	if ( wp_is_post_revision( $post->ID ) ) {
		return;
	}

	// Only send if push is enabled, connected, and dedicated plugin is not active
	if ( '1' !== HYPWA_Options::get( 'cf_push_status', '0' ) || '1' !== HYPWA_Options::get( 'cf_push_connected', '0' ) || class_exists( 'HyperPushX' ) ) {
		return;
	}

	if ( '1' !== HYPWA_Options::get( 'cf_push_send_on_publish', '0' ) ) {
		return;
	}

	// We only send for public post types (e.g. post, page, etc.)
	$allowed_post_types = [ 'post', 'page' ];
	if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
		return;
	}

	$api_key      = HYPWA_Options::get( 'cf_push_api_key' );
	$website_uuid = HYPWA_Options::get( 'cf_push_website_uuid' );
	$website_id   = HYPWA_Options::get( 'cf_push_website_id' );

	if ( empty( $api_key ) || ( empty( $website_uuid ) && empty( $website_id ) ) ) {
		return;
	}

	$title   = sprintf( __( 'New post published: %s', 'hyper-pwa' ), get_the_title( $post ) );
	$message = __( 'Click here to read our latest post.', 'hyper-pwa' );
	$url     = get_permalink( $post );

	$api_url = HYPWA_PUSH_API_BASE . '/api/v1/campaigns';
	$payload = [
		'website_id'    => ! empty( $website_uuid ) ? $website_uuid : $website_id,
		'title'         => $title,
		'message'       => $message,
		'url'           => $url,
		'delivery_type' => 'immediate',
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

	wp_remote_post( $api_url, $args );
}

/**
 * Register shortcode [hypwa_install_button] to display a customizable PWA install button.
 */
add_shortcode( 'hypwa_install_button', 'hypwa_render_install_button_shortcode' );
function hypwa_render_install_button_shortcode( $atts ) {
    if ( '1' !== HYPWA_Options::get( 'cf_install_button_status', '0' ) ) {
        return '';
    }

    $atts = shortcode_atts( [
        'text' => HYPWA_Options::get( 'cf_ib_text', 'Install App' ),
    ], $atts );

    $bg_color      = HYPWA_Options::get( 'cf_ib_bg_color', '#2563eb' );
    $text_color    = HYPWA_Options::get( 'cf_ib_text_color', '#ffffff' );
    $border_radius = HYPWA_Options::get( 'cf_ib_border_radius', '8' );
    $padding       = HYPWA_Options::get( 'cf_ib_padding', '12px 24px' );

    // Ensure border-radius has unit
    if ( is_numeric( $border_radius ) ) {
        $border_radius .= 'px';
    }

    // Load frontend assets
    $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    wp_enqueue_script( 'hypwa-install-button-js', HYPWA_PLUGIN_URL . "assets/customer/js/install-button{$min}.js", [], HYPWA_VERSION, true );
    wp_enqueue_style( 'hypwa-install-button-css', HYPWA_PLUGIN_URL . "assets/customer/css/install-button{$min}.css", [], HYPWA_VERSION );
    wp_style_add_data( 'hypwa-install-button-css', 'rtl', true );

    ob_start();
    ?>
    <button type="button" 
            class="hypwa-install-btn-shortcode" 
            style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; border-radius: <?php echo esc_attr( $border_radius ); ?>; padding: <?php echo esc_attr( $padding ); ?>; display: none;">
        <?php echo esc_html( $atts['text'] ); ?>
    </button>
    <?php
    return ob_get_clean();
}

/**
 * Register Elementor Widget for PWA Install Button.
 */
add_action( 'elementor/widgets/register', 'hypwa_register_elementor_widget' );
function hypwa_register_elementor_widget( $widgets_manager ) {
    if ( '1' !== HYPWA_Options::get( 'cf_elementor_widget_status', '0' ) ) {
        return;
    }
    if ( ! class_exists( 'Elementor\Widget_Base' ) ) {
        return;
    }
    require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/compatibility/class-hypwa-elementor-widget.php';
    $widgets_manager->register( new \HYPWA_Elementor_Widget() );
}

/**
 * Register Gutenberg Block for PWA Install Button.
 */
add_action( 'init', 'hypwa_register_gutenberg_block' );
function hypwa_register_gutenberg_block() {
    if ( '1' !== HYPWA_Options::get( 'cf_gutenberg_block_status', '0' ) ) {
        return;
    }

    $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

    // Register script for block editor
    wp_register_script(
        'hypwa-install-button-block-js',
        HYPWA_PLUGIN_URL . "assets/admin/js/install-button-block{$min}.js",
        [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-block-editor' ],
        HYPWA_VERSION,
        true
    );

    // Register block type
    register_block_type( 'hyper-pwa/install-button', [
        'editor_script' => 'hypwa-install-button-block-js',
        'render_callback' => 'hypwa_render_install_button_block',
    ] );
}

/**
 * Gutenberg block render callback.
 */
function hypwa_render_install_button_block( $attributes ) {
    if ( '1' !== HYPWA_Options::get( 'cf_gutenberg_block_status', '0' ) ) {
        return '';
    }

    $text          = isset( $attributes['text'] ) ? wp_kses_post( $attributes['text'] ) : HYPWA_Options::get( 'cf_ib_text', 'Install App' );
    $bg_color      = isset( $attributes['bgColor'] ) ? $attributes['bgColor'] : HYPWA_Options::get( 'cf_ib_bg_color', '#2563eb' );
    $text_color    = isset( $attributes['textColor'] ) ? $attributes['textColor'] : HYPWA_Options::get( 'cf_ib_text_color', '#ffffff' );
    
    // Border Radius (Numeric)
    $border_radius = isset( $attributes['borderRadius'] ) ? intval( $attributes['borderRadius'] ) : intval( HYPWA_Options::get( 'cf_ib_border_radius', '8' ) );
    
    // Padding (Numeric Vertical and Horizontal)
    $padding_v     = isset( $attributes['paddingVertical'] ) ? intval( $attributes['paddingVertical'] ) : 12;
    $padding_h     = isset( $attributes['paddingHorizontal'] ) ? intval( $attributes['paddingHorizontal'] ) : 24;

    // Check if block attributes are unset and we should fallback to DB text strings for padding
    if ( ! isset( $attributes['paddingVertical'] ) && ! isset( $attributes['paddingHorizontal'] ) ) {
        // Fallback to the saved general settings string (e.g. "12px 24px")
        $padding = HYPWA_Options::get( 'cf_ib_padding', '12px 24px' );
    } else {
        $padding = $padding_v . 'px ' . $padding_h . 'px';
    }

    // Alignment
    $align         = isset( $attributes['align'] ) ? $attributes['align'] : 'center';
    $align         = in_array( $align, [ 'left', 'center', 'right' ] ) ? $align : 'center';

    // Strict sanitization for block attributes to prevent any style tag breakout or CSS injection
    $bg_color      = preg_replace( '/[^#a-zA-Z0-9(),\s\-]/', '', $bg_color );
    $text_color    = preg_replace( '/[^#a-zA-Z0-9(),\s\-]/', '', $text_color );
    $border_radius = preg_replace( '/[^a-zA-Z0-9%\s]/', '', $border_radius );
    $padding       = preg_replace( '/[^a-zA-Z0-9%\s]/', '', $padding );

    // Ensure border-radius has unit
    if ( is_numeric( $border_radius ) ) {
        $border_radius .= 'px';
    }

    // Load frontend assets
    $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    wp_enqueue_script( 'hypwa-install-button-js', HYPWA_PLUGIN_URL . "assets/customer/js/install-button{$min}.js", [], HYPWA_VERSION, true );
    wp_enqueue_style( 'hypwa-install-button-css', HYPWA_PLUGIN_URL . "assets/customer/css/install-button{$min}.css", [], HYPWA_VERSION );
    wp_style_add_data( 'hypwa-install-button-css', 'rtl', true );

    ob_start();
    ?>
    <div class="hypwa-block-install-button-wrapper" style="text-align: <?php echo esc_attr( $align ); ?>;">
        <button type="button" 
                class="hypwa-install-btn-shortcode" 
                style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; border-radius: <?php echo esc_attr( $border_radius ); ?>; padding: <?php echo esc_attr( $padding ); ?>; display: none;">
            <?php echo wp_kses_post( $text ); ?>
        </button>
    </div>
    <?php
    return ob_get_clean();
}