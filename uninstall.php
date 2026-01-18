<?php
/**
 * Uninstall * Uninstall My Tiny Stats
 * 
 * Fired when the plugin is uninstalled.
 * It ensures that the custom database table is dropped and no data is left behind.
 *
 * @package My_Tiny_Stats
 */

// If uninstall not called from WordPress, then exit.
// 如果不是由 WordPress 呼叫此檔案，則離開。
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// 1. Drop the custom configuration table
// 1. 移除自定義設定資料表
$table_name = $wpdb->prefix . 'mtdss_config';
// WPCS: Prepared SQL or trusted variable.
// Using explicit query for DROP TABLE, verifying table name prefix.
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

// 2. Delete the transient cache
// 2. 刪除快取
delete_transient( 'mtdss_large_files_cache' );
