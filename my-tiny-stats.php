<?php
/**
 * Plugin Name: My Tiny Stats
 * Plugin URI:  https://github.com/tonny-tw/my-tiny-stats
 * Description: Display server disk space statistics with a visual progress bar in the admin menu.
 * Version:     1.4.0
 * Author:      Tonny
 * Author URI:  https://github.com/tonny-tw
 * License:     GPL-2.0+
 * Text Domain: my-tiny-stats
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Constants
define( 'MTDSS_VERSION', '1.4.0' );
define( 'MTDSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MTDSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader (Simple, manual for now as per refactor plan)
require_once MTDSS_PLUGIN_DIR . 'includes/class-mtdss-data.php';
require_once MTDSS_PLUGIN_DIR . 'includes/class-mtdss-settings.php';
require_once MTDSS_PLUGIN_DIR . 'admin/class-mtdss-admin.php';

// Initialize Admin Controller
function mtdss_init_plugin() {
    $admin = new MTDSS_Admin();
    $admin->init();
}
add_action( 'plugins_loaded', 'mtdss_init_plugin' );

/**
 * Activation Hook
 * Creates/Updates DB Table
 */
function mtdss_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'mtdss_config';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        setting_name varchar(255) NOT NULL,
        setting_value longtext NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY setting_name (setting_name)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Default Settings
    if ( ! MTDSS_Settings::get_config( 'db_version' ) ) {
        MTDSS_Settings::update_config( 'db_version', '1.3' );
        MTDSS_Settings::update_config( 'min_file_size', '10' );
        MTDSS_Settings::update_config( 'file_limit', '10' );
        MTDSS_Settings::update_config( 'recent_days', '7' );
        MTDSS_Settings::update_config( 'suspicious_extensions', 'php,exe,sh' );
    }
}
register_activation_hook( __FILE__, 'mtdss_activate' );



// Legacy File Retirement Note:
// includes/mtdss-core.php is no longer loaded.
