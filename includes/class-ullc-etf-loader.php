<?php

class ULLC_ETF_Loader {

    protected $actions;
    protected $filters;

    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        // Add init action
        add_action('init', array($this, 'init'));
        
        // Add admin menu and assets
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }

        // Add AJAX handlers
        add_action('wp_ajax_ullc_etf_scan', array($this, 'handle_scan_request'));
    }

    public function init() {
        // Initialize plugin functionality
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Email Template Finder', 'ullc-email-template-finder'),
            __('Email Templates', 'ullc-email-template-finder'),
            'manage_options',
            'ullc-email-template-finder',
            array($this, 'display_admin_page'),
            'dashicons-email-alt'
        );
    }

    public function enqueue_admin_assets($hook) {
        // Only load on our admin page
        if ('toplevel_page_ullc-email-template-finder' !== $hook) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'ullc-etf-admin-style',
            ULLC_ETF_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            ULLC_ETF_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'ullc-etf-admin-script',
            ULLC_ETF_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            ULLC_ETF_VERSION,
            true
        );

        // Localize script with necessary data
        wp_localize_script(
            'ullc-etf-admin-script',
            'ullc_etf',
            array(
                'nonce' => wp_create_nonce('ullc_etf_scan_nonce'),
                'scanning_text' => __('Scanning...', 'ullc-email-template-finder'),
                'error_messages' => array(
                    'scan_failed' => __('Scan failed. Please try again.', 'ullc-email-template-finder'),
                    'no_results' => __('No email templates found.', 'ullc-email-template-finder'),
                    'permission_denied' => __('You do not have permission to perform this action.', 'ullc-email-template-finder'),
                )
            )
        );
    }

    public function display_admin_page() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'ullc-email-template-finder'));
        }

        // Include the admin page template
        ?>
        <div class="wrap">
            <h1><?php _e('Email Template Finder', 'ullc-email-template-finder'); ?></h1>
            
            <div class="ullc-etf-scan-controls">
                <button type="button" class="button button-primary" id="ullc-etf-scan-button">
                    <?php _e('Scan for Email Templates', 'ullc-email-template-finder'); ?>
                </button>
                <span class="spinner"></span>
            </div>

            <div class="ullc-etf-progress"></div>
            <div class="ullc-etf-results"></div>
        </div>
        <?php
    }

    public function handle_scan_request() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ullc_etf_scan_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'ullc-email-template-finder')
            ));
        }

        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Permission denied.', 'ullc-email-template-finder')
            ));
        }

        // Scanning functionality will be added here later
        wp_send_json_success(array(
            'templates' => array()
        ));
    }

    public function run() {
        // Run all registered hooks
    }
}