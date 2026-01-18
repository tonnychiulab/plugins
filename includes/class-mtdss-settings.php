<?php
/**
 * My Tiny Stats - Settings Handling Class
 * 
 * Manages configuration via custom DB table.
 * 
 * @package My_Tiny_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MTDSS_Settings {

    /**
     * Get config value.
     */
    public static function get_config( $key, $default = '' ) {
        global $wpdb;
        
        $group = 'mtdss_config';
        $cached = wp_cache_get( $key, $group );
        if ( false !== $cached ) {
            return $cached;
        }

        $table_name = $wpdb->prefix . 'mtdss_config';
        // Note: In early load or if table missing, this might fail unless checked.
        // We assume activation hook created it.
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM $table_name WHERE setting_name = %s", $key ) );
        
        $result = ( $value !== null ) ? $value : $default;
        
        wp_cache_set( $key, $result, $group );
        
        return $result;
    }

    /**
     * Update config value.
     */
    public static function update_config( $key, $value ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mtdss_config';
        
        $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE setting_name = %s", $key ) );

        if ( $existing ) {
            $wpdb->update( $table_name, array( 'setting_value' => $value ), array( 'setting_name' => $key ) );
        } else {
            $wpdb->insert( $table_name, array( 'setting_name' => $key, 'setting_value' => $value ) );
        }
        
        wp_cache_delete( $key, 'mtdss_config' );
    }

    /**
     * Delete Cache (Refresh)
     */
    public static function clear_transients() {
        delete_transient( 'mtdss_large_files_cache' );
        delete_transient( 'mtdss_dir_stats_cache' );
        delete_transient( 'mtdss_db_stats_cache' );
    }
}
