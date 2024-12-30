<?php
/**
 * Email Template Scanner Class
 *
 * @package    ULLC_Email_Template_Finder
 * @subpackage ULLC_Email_Template_Finder/includes
 */

class ULLC_ETF_Scanner {
    /**
     * Known email sending functions in WordPress/WooCommerce
     */
    private $email_functions = array(
        'wp_mail',
        'wc_mail',
        'WC_Email::send',
        'PHPMailer::send'
    );

    /**
     * Store scan results
     */
    private $results = array();

    /**
     * Scan all possible locations for email templates
     *
     * @return array Scan results
     */
    public function scan_all() {
        // Scan plugins
        $this->scan_plugins();
        
        // Scan active theme
        $this->scan_theme();
        
        // Scan database
        $this->scan_database();
        
        return $this->results;
    }

    /**
     * Scan plugins directory for email templates
     */
    private function scan_plugins() {
        $plugins_dir = WP_PLUGIN_DIR;
        $active_plugins = get_option('active_plugins');

        foreach ($active_plugins as $plugin) {
            $plugin_dir = dirname($plugins_dir . '/' . $plugin);
            $this->scan_directory($plugin_dir, 'plugin', $plugin);
        }
    }

    /**
     * Scan active theme for email templates
     */
    private function scan_theme() {
        $theme_dir = get_template_directory();
        $this->scan_directory($theme_dir, 'theme', get_template());
    }

    /**
     * Scan a directory recursively for PHP files
     *
     * @param string $dir Directory path
     * @param string $type Source type (plugin|theme)
     * @param string $source Source identifier
     */
    private function scan_directory($dir, $type, $source) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->scan_file($file->getPathname(), $type, $source);
            }
        }
    }

    /**
     * Scan individual PHP file for email templates
     *
     * @param string $file File path
     * @param string $type Source type
     * @param string $source Source identifier
     */
    private function scan_file($file, $type, $source) {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line_number => $line) {
            // Check for email functions
            foreach ($this->email_functions as $func) {
                if (strpos($line, $func) !== false) {
                    // Found email sending function
                    $template_line = $this->find_template_definition($lines, $line_number);
                    $trigger = $this->find_trigger($lines, $line_number);
                    
                    $this->results[] = array(
                        'type' => $type,
                        'location' => $source,
                        'file' => str_replace(ABSPATH, '', $file),
                        'send_line_number' => $line_number + 1,
                        'template_line_number' => $template_line,
                        'trigger' => $trigger
                    );
                }
            }
        }
    }

    /**
     * Scan database for email templates
     */
    private function scan_database() {
        global $wpdb;
        
        // Check options table for email settings
        $email_options = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options}
            WHERE option_name LIKE '%mail%' 
            OR option_name LIKE '%email%'
        ");

        foreach ($email_options as $option) {
            if (is_serialized($option->option_value)) {
                $value = unserialize($option->option_value);
                if ($this->is_email_template($value)) {
                    $this->results[] = array(
                        'type' => 'settings',
                        'location' => 'wp_options',
                        'file' => $option->option_name,
                        'send_line_number' => null,
                        'template_line_number' => null,
                        'trigger' => 'option: ' . $option->option_name
                    );
                }
            }
        }

        // Check posts table for email templates
        $email_posts = $wpdb->get_results("
            SELECT ID, post_title, post_content 
            FROM {$wpdb->posts}
            WHERE post_type = 'wc_email_template'
            OR post_content LIKE '%[wc_email%'
        ");

        foreach ($email_posts as $post) {
            $this->results[] = array(
                'type' => 'post',
                'location' => 'wp_posts',
                'file' => 'post_id: ' . $post->ID,
                'send_line_number' => null,
                'template_line_number' => null,
                'trigger' => 'post: ' . $post->post_title
            );
        }
    }

    /**
     * Find template definition before email send
     *
     * @param array $lines File contents by line
     * @param int $send_line Line number where email is sent
     * @return int|null Template definition line number
     */
    private function find_template_definition($lines, $send_line) {
        // Look up to 10 lines before send for template definition
        $start = max(0, $send_line - 10);
        
        for ($i = $send_line; $i >= $start; $i--) {
            if (preg_match('/(template|message|body|html)/i', $lines[$i])) {
                return $i + 1;
            }
        }
        
        return null;
    }

    /**
     * Find trigger (hook/action) for email send
     *
     * @param array $lines File contents by line
     * @param int $send_line Line number where email is sent
     * @return string|null Trigger information
     */
    private function find_trigger($lines, $send_line) {
        // Look up to 20 lines before send for add_action/add_filter
        $start = max(0, $send_line - 20);
        
        for ($i = $send_line; $i >= $start; $i--) {
            if (preg_match('/add_(action|filter)\s*\(\s*[\'"]([^\'"]+)[\'"]/i', $lines[$i], $matches)) {
                return $matches[2];
            }
        }
        
        return null;
    }

    /**
     * Check if a value appears to be an email template
     *
     * @param mixed $value Value to check
     * @return bool
     */
    private function is_email_template($value) {
        if (is_string($value)) {
            return (
                strpos($value, '{email') !== false ||
                strpos($value, '[email') !== false ||
                strpos($value, '<!DOCTYPE') !== false ||
                strpos($value, '<html') !== false
            );
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if ($this->is_email_template($item)) {
                    return true;
                }
            }
        }
        return false;
    }
}