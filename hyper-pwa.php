<?php
/*
Plugin Name: Hyper PWA - Bringing Modern Progressive Web App Experiences
Description: Turn your WordPress site into a fast, installable Progressive Web App. Give users a seamless offline experience and native mobile app feel.
Version: 5.7
Text Domain: hyper-pwa
Domain Path: /languages
Author: amanstacker
Author URI: https://profiles.wordpress.org/amanstacker/
License: GPLv2 or later
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HYPWA_VERSION', '5.7' );
define( 'HYPWA_DIR_NAME_FILE', __FILE__  );
define( 'HYPWA_DIR_NAME', dirname(  __FILE__  ) );
define( 'HYPWA_DIR_URI', plugin_dir_url( __FILE__  ));
define( 'HYPWA_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

define('HYPWA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('HYPWA_PLUGIN_BASENAME', plugin_basename(__FILE__ ) );
define('HYPWA_PUSH_API_BASE', 'https://hyperpushx.com');

// Admin Settings
require_once HYPWA_PLUGIN_DIR_PATH . 'feedback/feedback.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/common-functions.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/admin/class-hypwa-settings.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/class-hypwa-options.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/admin/class-hypwa-file-serving.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-hypwa-settings-ajax.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/admin/settings/class-hypwa-migrate-settings.php';

// Customer Files
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/helper.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/manifest.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/service_worker/template.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/compatibility/onesignal.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/compatibility/webpushr.php';
require_once HYPWA_PLUGIN_DIR_PATH . 'includes/customer/bootstrap.php';

add_filter( 'plugin_action_links_' . HYPWA_PLUGIN_BASENAME, 'hypwap_plugin_action_links_clbk' );

function hypwap_plugin_action_links_clbk( $actions ) {

     $dashboard_url = admin_url( 'admin.php?page=hypwa-settings' );
     $docs_url = esc_url( "https://hyperpwa.com/knowledge-base/" );
     $actions[]  = '<a href="' . esc_url( $dashboard_url ) . '">' . esc_html__( 'Dashboard', 'hyper-pwa-premium' ) . '</a>';
     $actions[]  = '<a href="' . esc_url( $docs_url ) . '">' . esc_html__( 'Knowledge Base', 'hyper-pwa-premium' ) . '</a>';
     if ( ! defined('HYPWAP_VERSION') ) {
     	$actions[]  = '<a href="' . esc_url( 'https://hyperpwa.com/premium/' ) . '">' . esc_html__( 'Upgrade to Premium', 'hyper-pwa-premium' ) . '</a>';
     }
     return $actions;
}