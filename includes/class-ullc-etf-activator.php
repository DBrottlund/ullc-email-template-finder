<?php

class ULLC_ETF_Activator {

    public static function activate() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ullc_email_templates';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            location varchar(255) NOT NULL,
            file varchar(255),
            send_line_number int,
            template_line_number int,
            trigger varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set version in options
        add_option('ullc_etf_db_version', ULLC_ETF_VERSION);
    }
}