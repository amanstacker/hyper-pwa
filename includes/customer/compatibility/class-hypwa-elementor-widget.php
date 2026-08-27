<?php
/**
 * Elementor PWA Install Button Widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HYPWA_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'hyper-pwa-install-button';
    }

    public function get_title() {
        return esc_html__( 'PWA Install Button', 'hyper-pwa' );
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ 'pwa', 'install', 'app', 'button' ];
    }

    protected function register_controls() {
        // Content Tab
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Content', 'hyper-pwa' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => esc_html__( 'Button Text', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => HYPWA_Options::get( 'cf_ib_text', 'Install App' ),
                'placeholder' => esc_html__( 'Type your button text here', 'hyper-pwa' ),
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Style', 'hyper-pwa' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'bg_color',
            [
                'label' => esc_html__( 'Background Color', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => HYPWA_Options::get( 'cf_ib_bg_color', '#2563eb' ),
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__( 'Text Color', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => HYPWA_Options::get( 'cf_ib_text_color', '#ffffff' ),
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => intval( HYPWA_Options::get( 'cf_ib_border_radius', '8' ) ),
                ],
            ]
        );

        $this->add_control(
            'padding_vertical',
            [
                'label' => esc_html__( 'Vertical Padding (px)', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
            ]
        );

        $this->add_control(
            'padding_horizontal',
            [
                'label' => esc_html__( 'Horizontal Padding (px)', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 24,
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__( 'Alignment', 'hyper-pwa' ),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'hyper-pwa' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'hyper-pwa' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'hyper-pwa' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $text = ! empty( $settings['button_text'] ) ? $settings['button_text'] : HYPWA_Options::get( 'cf_ib_text', 'Install App' );
        $bg_color = ! empty( $settings['bg_color'] ) ? $settings['bg_color'] : HYPWA_Options::get( 'cf_ib_bg_color', '#2563eb' );
        $text_color = ! empty( $settings['text_color'] ) ? $settings['text_color'] : HYPWA_Options::get( 'cf_ib_text_color', '#ffffff' );

        $border_radius_size = isset( $settings['border_radius']['size'] ) ? intval( $settings['border_radius']['size'] ) : intval( HYPWA_Options::get( 'cf_ib_border_radius', '8' ) );
        $border_radius_unit = isset( $settings['border_radius']['unit'] ) ? $settings['border_radius']['unit'] : 'px';
        $border_radius = $border_radius_size . $border_radius_unit;

        $padding_v = isset( $settings['padding_vertical'] ) ? intval( $settings['padding_vertical'] ) : 12;
        $padding_h = isset( $settings['padding_horizontal'] ) ? intval( $settings['padding_horizontal'] ) : 24;
        $padding = $padding_v . 'px ' . $padding_h . 'px';

        $align = ! empty( $settings['align'] ) ? $settings['align'] : 'center';

        // Enqueue styles & scripts
        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script( 'hypwa-install-button-js', HYPWA_PLUGIN_URL . "assets/customer/js/install-button{$min}.js", [], HYPWA_VERSION, true );
        wp_enqueue_style( 'hypwa-install-button-css', HYPWA_PLUGIN_URL . "assets/customer/css/install-button{$min}.css", [], HYPWA_VERSION );

        ?>
        <div class="hypwa-block-install-button-wrapper" style="text-align: <?php echo esc_attr( $align ); ?>;">
            <button type="button" 
                    class="hypwa-install-btn-shortcode" 
                    style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; border-radius: <?php echo esc_attr( $border_radius ); ?>; padding: <?php echo esc_attr( $padding ); ?>; display: none;">
                <?php echo wp_kses_post( $text ); ?>
            </button>
        </div>
        <?php
    }
}
