<?php
/**
 * Admin Display Class
 *
 * @package    ULLC_Email_Template_Finder
 * @subpackage ULLC_Email_Template_Finder/admin
 */

class ULLC_ETF_Admin_Display {
    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'handle_scan_action'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        add_options_page(
            __('Email Template Finder', 'ullc-email-template-finder'),
            __('Email Templates', 'ullc-email-template-finder'),
            'manage_options',
            'ullc-email-template-finder',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Handle scan action
     */
    public function handle_scan_action() {
        if (isset($_POST['action']) && $_POST['action'] === 'ullc_etf_scan') {
            check_admin_referer('ullc_etf_scan');
            
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ullc-etf-scanner.php';
            $scanner = new ULLC_ETF_Scanner();
            $results = $scanner->scan_all();
            
            update_option('ullc_etf_scan_results', $results);
            update_option('ullc_etf_last_scan', current_time('mysql'));
            
            wp_redirect(add_query_arg('scan', 'complete', wp_get_referer()));
            exit;
        }
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        $results = get_option('ullc_etf_scan_results', array());
        $last_scan = get_option('ullc_etf_last_scan');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Email Template Finder', 'ullc-email-template-finder'); ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('ullc_etf_scan'); ?>
                <input type="hidden" name="action" value="ullc_etf_scan">
                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__('Scan for Email Templates', 'ullc-email-template-finder'); ?>
                    </button>
                </p>
            </form>

            <?php if ($last_scan): ?>
                <p>
                    <?php printf(
                        esc_html__('Last scan: %s', 'ullc-email-template-finder'),
                        date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_scan))
                    ); ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($results)): ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Type', 'ullc-email-template-finder'); ?></th>
                            <th><?php echo esc_html__('Location', 'ullc-email-template-finder'); ?></th>
                            <th><?php echo esc_html__('File', 'ullc-email-template-finder'); ?></th>
                            <th><?php echo esc_html__('Send Line', 'ullc-email-template-finder'); ?></th>
                            <th><?php echo esc_html__('Template Line', 'ullc-email-template-finder'); ?></th>
                            <th><?php echo esc_html__('Trigger', 'ullc-email-template-finder'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['type']); ?></td>
                                <td><?php echo esc_html($result['location']); ?></td>
                                <td><?php echo esc_html($result['file']); ?></td>
                                <td><?php echo $result['send_line_number'] ? esc_html($result['send_line_number']) : '-'; ?></td>
                                <td><?php echo $result['template_line_number'] ? esc_html($result['template_line_number']) : '-'; ?></td>
                                <td><?php echo esc_html($result['trigger'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($last_scan): ?>
                <p><?php echo esc_html__('No email templates found.', 'ullc-email-template-finder'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}