<?php

class ULLC_ETF_Loader {

    protected $actions;
    protected $filters;

    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        // Add init action
        add_action('init', array($this, 'init'));
        
        // Add admin menu
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }
    }

    public function init() {
        // Initialize plugin functionality
    }

    public function add_admin_menu() {
        add_menu_page(
            'Email Template Finder',
            'Email Templates',
            'manage_options',
            'ullc-email-template-finder',
            array($this, 'display_admin_page'),
            'dashicons-email-alt'
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_ullc-email-template-finder' !== $hook) {
            return;
        }

        wp_enqueue_style('ullc-etf-admin-style', ULLC_ETF_PLUGIN_URL . 'admin/css/admin.css', array(), ULLC_ETF_VERSION);
        wp_enqueue_script('ullc-etf-admin-script', ULLC_ETF_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), ULLC_ETF_VERSION, true);
    }

    public function display_admin_page() {
        // Admin page content will be added later
        echo '<div class="wrap">';
        echo '<h1>Email Template Finder</h1>';
        echo '<div class="ullc-etf-scan-controls">';
        echo '<button class="button button-primary" id="ullc-etf-scan-button">Scan for Email Templates</button>';
        echo '</div>';
        echo '<div class="ullc-etf-results"></div>';
        echo '</div>';
    }

    public function run() {
        // Run all registered hooks
    }
}