<?php
/**
 * Plugin Name: ULLC Email Template Finder
 * Plugin URI: https://github.com/DBrottlund/ullc-email-template-finder
 * Description: Scans and discovers all email templates and their triggers in a WordPress/WooCommerce installation
 * Version: 0.0.1
 * Author: ULLC
 * Author URI: https://github.com/DBrottlund
 * Text Domain: ullc-email-template-finder
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ULLC_ETF_VERSION', '0.0.1');
define('ULLC_ETF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ULLC_ETF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load translations
function ullc_etf_load_textdomain() {
    load_plugin_textdomain(
        'ullc-email-template-finder',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'ullc_etf_load_textdomain', 5);

// Include core classes
require_once ULLC_ETF_PLUGIN_DIR . 'includes/class-ullc-etf-activator.php';
require_once ULLC_ETF_PLUGIN_DIR . 'includes/class-ullc-etf-deactivator.php';
require_once ULLC_ETF_PLUGIN_DIR . 'includes/class-ullc-etf-loader.php';
require_once ULLC_ETF_PLUGIN_DIR . 'includes/class-ullc-etf-scanner.php';
require_once ULLC_ETF_PLUGIN_DIR . 'admin/class-ullc-etf-admin-display.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('ULLC_ETF_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('ULLC_ETF_Deactivator', 'deactivate'));

// Initialize the plugin
add_action('plugins_loaded', 'ullc_etf_init', 10);

function ullc_etf_init() {
    $loader = new ULLC_ETF_Loader();
    
    // Initialize admin display if in admin area
    if (is_admin()) {
        new ULLC_ETF_Admin_Display();
    }
    
    $loader->run();
}