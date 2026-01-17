<?php
/*
Plugin Name: Disk Space Stats
Description: Display server disk space statistics with a visual progress bar in the admin menu.
Version: 1.2
Text Domain: disk-space-stats
Domain Path: /languages
*/

// Prevent direct file access
// 防止直接存取檔案
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add the plugin's menu item to the WordPress admin.
 * 新增外掛選單項目到 WordPress 後台。
 */
function dss_add_disk_space_stats_menu() {
    add_menu_page(
        __( 'Disk Space Stats', 'disk-space-stats' ), // Page Title (i18n)
        __( 'Disk Space', 'disk-space-stats' ),       // Menu Title (i18n)
        'manage_options',                             // Capability
        'disk-space-stats',                           // Slug
        'dss_display_disk_space_stats',               // Callback with prefix
        'dashicons-chart-pie',                        // Icon
        100                                           // Position
    );
}
add_action( 'admin_menu', 'dss_add_disk_space_stats_menu' );

/**
 * Display the content of the disk space stats page.
 * 顯示硬碟空間統計頁面的內容。
 */
function dss_display_disk_space_stats() {
    // Check user capabilities for security
    // 檢查使用者權限
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get total server disk space
    // 取得伺服器硬碟的總空間
    $total_space = disk_total_space( __DIR__ );
    
    // Get free server disk space
    // 取得伺服器硬碟的可用空間
    $free_space = disk_free_space( __DIR__ );

    // Calculate used space
    // 計算已使用的空間
    $used_space = $total_space - $free_space;

    // Calculate usage percentage
    // 計算已使用空間的百分比
    $used_percent = ( $total_space > 0 ) ? round( ( $used_space / $total_space ) * 100, 2 ) : 0;
    
    // Calculate free percentage
    // 計算可用空間的百分比
    $free_percent = 100 - $used_percent;

    // Format numbers to GB
    // 格式化數字為 GB
    $total_space_gb = round( $total_space / ( 1024 * 1024 * 1024 ), 2 );
    $free_space_gb  = round( $free_space / ( 1024 * 1024 * 1024 ), 2 );
    $used_space_gb  = round( $used_space / ( 1024 * 1024 * 1024 ), 2 );

    // Determine progress bar color
    // 決定進度條顏色
    $bar_color = ( $used_percent > 90 ) ? '#d63638' : '#2271b1';

    // Output HTML with escaping
    // 輸出 HTML 並進行跳脫處理
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Disk Space Statistics', 'disk-space-stats' ); ?></h1>
        
        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
            <h2 class="title"><?php esc_html_e( 'Server Storage Usage', 'disk-space-stats' ); ?></h2>
            
            <p><?php esc_html_e( 'Below is the current usage of the server\'s disk space.', 'disk-space-stats' ); ?></p>
            
            <div style="background: #f0f0f1; border-radius: 4px; height: 30px; width: 100%; margin: 20px 0; border: 1px solid #c3c4c7; overflow: hidden;">
                <div style="background: <?php echo esc_attr( $bar_color ); ?>; height: 100%; width: <?php echo esc_attr( $used_percent ); ?>%; line-height: 30px; color: #fff; text-align: center; font-weight: bold; transition: width 0.5s ease;">
                    <?php echo esc_html( $used_percent ); ?>%
                </div>
            </div>

            <table class="widefat fixed striped" style="border: none;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Status', 'disk-space-stats' ); ?></th>
                        <th><?php esc_html_e( 'Size (GB)', 'disk-space-stats' ); ?></th>
                        <th><?php esc_html_e( 'Percentage', 'disk-space-stats' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="color: <?php echo esc_attr( $bar_color ); ?>; font-weight: bold;"><?php esc_html_e( 'Used Space', 'disk-space-stats' ); ?></td>
                        <td><?php echo esc_html( $used_space_gb ); ?> GB</td>
                        <td><?php echo esc_html( $used_percent ); ?>%</td>
                    </tr>
                    <tr>
                        <td style="color: #46b450; font-weight: bold;"><?php esc_html_e( 'Free Space', 'disk-space-stats' ); ?></td>
                        <td><?php echo esc_html( $free_space_gb ); ?> GB</td>
                        <td><?php echo esc_html( $free_percent ); ?>%</td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e( 'Total Capacity', 'disk-space-stats' ); ?></strong></td>
                        <td><strong><?php echo esc_html( $total_space_gb ); ?> GB</strong></td>
                        <td>100%</td>
                    </tr>
                </tbody>
            </table>
            
            <p class="description" style="margin-top: 20px;">
                <?php esc_html_e( '* Data reflects real-time server status.', 'disk-space-stats' ); ?>
            </p>
        </div>
    </div>
    <?php
}
