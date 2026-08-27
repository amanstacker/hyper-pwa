<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

function hypwa_is_plugins_page() {

    if ( function_exists( 'get_current_screen' ) ) {

        $screen = get_current_screen();

            if ( is_object( $screen ) ) {

                if ( $screen->id == 'plugins' || $screen->id == 'plugins-network' ) {
                    return true;
                }

            }
    }

    return false;
}

add_filter( 'admin_footer', 'hypwa_deactivation_feedback_modal' );

function hypwa_deactivation_feedback_modal() {

    if ( is_admin() && hypwa_is_plugins_page() ) {

        $email = '';

        if ( function_exists( 'wp_get_current_user' ) ) {

            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User ) {
                $email = trim( $current_user->user_email );	
            }

        }
        
        ?>

<div id="hypwa-feedback-overlay" style="display: none;">
	
    <div id="hypwa-feedback-content">
		<div class="hypwa-dp-header">
            <h3><?php esc_html_e('Deactivating Hyper PWA', 'hyper-pwa') ?></h3>
            <button class="close dashicons dashicons-no hypwa-fd-stop-deactivation">
                <span class="screen-reader-text"></span>
            </button>
        </div>
    	<form action="" method="post">
		<div class="hypwa-dp-body">
	    <p><strong><?php esc_html_e('Help us improve — why are you deactivating the plugin?', 'hyper-pwa'); ?></strong></p>
        <ul class="hypwa-dp-reasons">
            <li>
                <input type="radio" id="hypwa-reason1" name="hypwa_disable_reason" value="temporary" />
                <label for="hypwa-reason1"><?php esc_html_e('The deactivation is temporary', 'hyper-pwa') ?></label>
            </li>
            <li>
                <input type="radio" id="hypwa-reason2" name="hypwa_disable_reason" value="stopped_using" />
                <label for="hypwa-reason2"><?php esc_html_e('I don\'t need PWA functionality anymore', 'hyper-pwa') ?></label>
            </li>
            <li>
                <input type="radio" id="hypwa-reason3" name="hypwa_disable_reason" value="missing_feature" />
                <label for="hypwa-reason3"><?php esc_html_e('Needed feature not available', 'hyper-pwa') ?></label>
            </li>
            <li>
                <input type="radio" id="hypwa-reason4" name="hypwa_disable_reason" value="technical_difficulties" />
                <label for="hypwa-reason4"><?php esc_html_e('Facing technical difficulties', 'hyper-pwa') ?></label>
            </li>
            <li>
                <input type="radio" id="hypwa-reason5" name="hypwa_disable_reason" value="switched_plugin" />
                <label for="hypwa-reason5"><?php esc_html_e('Switched to a different plugin', 'hyper-pwa') ?></label>
            </li>
            <li>
                <input type="radio" id="hypwa-reason6" name="hypwa_disable_reason" value="other_reason" />
                <label for="hypwa-reason6"><?php esc_html_e('Other reason', 'hyper-pwa') ?></label>
            </li>
        </ul>
        <p class="hypwa-feedback-note">
            <strong><?php esc_html_e('Heads up: ', 'hyper-pwa'); ?></strong>
            <?php esc_html_e( 'Clicking "Submit & Deactivate" will email your feedback to the developer. No site data is collected. Click "Skip & Deactivate" to deactivate without sending feedback.', 'hyper-pwa' ); ?>    
        </p>

	    <div class="hypwa-reason-details">
				<textarea data-id="hypwa-reason3" class="hypwa-d-none" rows="3" name="hypwa_missing_feature_text" placeholder="<?php esc_attr_e( 'Kindly describe the feature you found missing.', 'hyper-pwa' ); ?>"></textarea>
                <textarea data-id="hypwa-reason4" class="hypwa-d-none" rows="3" name="hypwa_technical_difficulties_text" placeholder="<?php esc_attr_e( 'Kindly provide details about the difficulties you\'re facing.', 'hyper-pwa' ); ?>"></textarea>
                <textarea data-id="hypwa-reason5" class="hypwa-d-none" rows="3" name="hypwa_switched_plugin_text" placeholder="<?php esc_attr_e( 'If you don\'t mind, name the plugin you switched to.', 'hyper-pwa' ); ?>"></textarea>
                <textarea data-id="hypwa-reason6" class="hypwa-d-none" rows="3" name="hypwa_other_reason_text" placeholder="<?php esc_attr_e( 'Kindly provide a brief explanation.', 'hyper-pwa' ); ?>"></textarea>
		</div>
		</div>
		<hr/>
		<div class="hypwa-dp-footer">
			<?php if( null !== $email && !empty( $email ) ) : ?>
    	    	<input type="hidden" name="hypwa_deactivated_from" value="<?php echo esc_attr($email); ?>" />
	    	<?php endif; ?>

			<input id="hypwa-feedback-submit" class="button button-primary" type="submit" name="hypwa_disable_submit" value="<?php esc_html_e('Submit & Deactivate', 'hyper-pwa'); ?>"/>
	    	<a class="button hypwa-only-deactivate"><?php esc_html_e('Skip & Deactivate', 'hyper-pwa'); ?></a>
	    	<a class="button hypwa-dt-de hypwa-fd-stop-deactivation"><?php esc_html_e('Don\'t Deactivate', 'hyper-pwa'); ?></a>
		</div>	    
	</form>
    </div>
</div>
<?php
        

    }
    
}


function hypwa_send_feedback() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die();
    }

        //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason : Since form is serialised nonce is verified after parsing the recieved data.
    if ( isset( $_POST['data'] ) ) {
        //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Reason : Since form is serialised nonce is verified after parsing the recieved data and we are not saving data here in db. 
        parse_str( wp_unslash( $_POST['data'] ), $form );
    }
    
    if ( ! isset( $form['hypwa_security_nonce'] ) || isset( $form['hypwa_security_nonce'] ) && !wp_verify_nonce( sanitize_text_field( $form['hypwa_security_nonce'] ), 'hypwa_ajax_check_nonce' ) ) {

        echo esc_html__('Nonce Not Verified', 'hyper-pwa');
        
        wp_die();
    }    
    
    $text = $subject = '';
        
    $headers = [];

    $from = isset( $form['hypwa_deactivated_from'] ) ? $form['hypwa_deactivated_from'] : '';

    if ( $from ) {
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
    }

    $reason = isset( $form['hypwa_disable_reason'] ) ? $form['hypwa_disable_reason'] : 'No Reason Given';

    switch ( $reason ) {

        case 'temporary':
            $subject = 'The deactivation is temporary';        
            $text    = 'The deactivation is temporary';
        break;
        case 'stopped_using':
            $subject = 'I don\'t need PWA functionality anymore';
            $text    = 'I don\'t need PWA functionality anymore';
        break;
        case 'missing_feature':
            $subject = 'Needed feature not available';
            if ( ! empty( $form['hypwa_missing_feature_text'] ) ) {
                $text    = $form['hypwa_missing_feature_text'];
            }
        
        break;
        case 'technical_difficulties':
            $subject = 'Facing Technical Difficulties';
            if ( ! empty( $form['hypwa_technical_difficulties_text'] ) ) {
                $text    = $form['hypwa_technical_difficulties_text'];
            }
        break;
        case 'switched_plugin':
            $subject = 'Switched to a different plugin';
            if ( ! empty( $form['hypwa_switched_plugin_text'] ) ) {
                $text    = $form['hypwa_switched_plugin_text'];
            }
        break;
        case 'other_reason':
            $subject = 'Other reason';
            if ( ! empty( $form['hypwa_other_reason_text'] ) ) {
                $text    = $form['hypwa_other_reason_text'];
            }
        break;        
        default:
            $subject = 'No Reason Given';
            $text    = 'No Reason Given';
        break;

    }
    
    wp_mail( 'support@hyperpwa.com', $subject, $text, $headers );
    
    echo 'sent';
    wp_die();

}

add_action( 'wp_ajax_hypwa_send_feedback', 'hypwa_send_feedback' );

function hypwa_enqueue_feedback_scripts() {

    if ( is_admin() && hypwa_is_plugins_page() ) {

        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style( 'hypwa-feedback-css', HYPWA_PLUGIN_URL . "feedback/feedback{$min}.css", false,  HYPWA_VERSION );
        wp_register_script( 'hypwa-feedback-js', HYPWA_PLUGIN_URL . "feedback/feedback{$min}.js", [ 'jquery' ],  HYPWA_VERSION, true );

         $localdata = [
                'ajax_url'      		       => admin_url( 'admin-ajax.php' ),
                'hypwa_security_nonce'          => wp_create_nonce( 'hypwa_ajax_check_nonce' )
         ];

        wp_localize_script( 'hypwa-feedback-js', 'hypwa_feedback_local', $localdata );
        wp_enqueue_script( 'hypwa-feedback-js' );
                
    }
    
}

add_action( 'admin_enqueue_scripts', 'hypwa_enqueue_feedback_scripts' );