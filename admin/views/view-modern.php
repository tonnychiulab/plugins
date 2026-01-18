<?php
if ( ! defined( 'ABSPATH' ) ) exit;

try {
    // Data Fetching
    $scan_results = MTDSS_Data::scan_filesystem();
    $db_stats = MTDSS_Data::get_database_usage( 100 );
    $disk_stats = MTDSS_Data::get_disk_usage(); 

    // Prepare Stats
    $used_space = $disk_stats ? $disk_stats['used'] : 0;
    $total_space = $disk_stats ? $disk_stats['total'] : 0;
    $used_pct = $disk_stats ? $disk_stats['pct'] : 0;

    $suspicious_count = count( $scan_results['suspicious_files'] ?? [] );
    $upload_dir = wp_upload_dir();

    // Tabs Logic
    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
    $base_url = admin_url( 'admin.php?page=my-tiny-stats&view_mode=modern' );

} catch ( Throwable $e ) {
    /* translators: %s: Error message */

    echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( 'Essential Data Error: %s', 'my-tiny-stats' ), esc_html( $e->getMessage() ) ) . '</p></div>';
    // Fallback data to prevent undefined variable errors below
    $scan_results = [];
    $db_stats = [];
    $disk_stats = [];
    $used_space = 0; $total_space = 0; $used_pct = 0;
    $suspicious_count = 0;
    $active_tab = 'overview';
    $base_url = admin_url('admin.php?page=my-tiny-stats');
    $upload_dir = wp_upload_dir();
}
?>

<div class="mtdss-modern-container" style="background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
    
    <!-- Shared Styles (Inline for simplicity during refactor, ideally move to admin.css) -->
    <style>
        .mtdss-modern-tab { padding: 15px 20px; text-decoration: none; color: #50575e; border-bottom: 2px solid transparent; display: inline-block; font-weight: 500; font-size: 14px; }
        .mtdss-modern-tab.active { color: #2271b1; border-bottom-color: #2271b1; }
        .mtdss-modern-tab:hover { color: #2271b1; }
        .mtdss-modern-content { padding: 20px; }
        .mtdss-modern-table { width: 100%; border-collapse: collapse; }
        .mtdss-modern-table th { text-align: left; padding: 10px; border-bottom: 1px solid #e2e4e7; color: #646970; font-weight: 500; font-size: 13px; }
        .mtdss-modern-table td { padding: 12px 10px; border-bottom: 1px solid #f0f0f1; font-size: 13px; color: #1d2327; }
        .mtdss-modern-table tr:last-child td { border-bottom: none; }
        .mtdss-modern-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        .mtdss-badge-red { background: #fee2e2; color: #991b1b; }
        .mtdss-badge-blue { background: #e0f2fe; color: #075985; }
        .mtdss-switch { position: relative; display: inline-block; width: 32px; height: 18px; }
        .mtdss-switch input { opacity: 0; width: 0; height: 0; }
        .mtdss-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 18px; }
        .mtdss-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .mtdss-slider { background-color: #2271b1; }
        input:checked + .mtdss-slider:before { transform: translateX(14px); }
    </style>

    <!-- Header Navigation -->
    <div style="border-bottom: 1px solid #e2e4e7; display: flex; justify-content: space-between; align-items: center; padding: 0 10px;">
        <div style="display: flex;">
            <?php
            $tabs = array(
                'overview' => __( 'Overview', 'my-tiny-stats' ),
                'files'    => __( 'Large Files', 'my-tiny-stats' ),
                'recent'   => __( 'Recent', 'my-tiny-stats' ), // New!
                'security' => __( 'Security', 'my-tiny-stats' ),
            );
            foreach ( $tabs as $k => $label ) {
                $cls = ( $active_tab === $k ) ? 'active' : '';
                echo '<a href="' . esc_url( add_query_arg( 'tab', $k, $base_url ) ) . '" class="mtdss-modern-tab ' . esc_attr( $cls ) . '">' . esc_html( $label ) . '</a>';
            }
            ?>
        </div>
        <div>
             <a href="#" class="button button-secondary disabled" style="border-radius: 4px;"><?php esc_html_e( 'Screen Options', 'my-tiny-stats' ); ?> <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px; vertical-align: middle;"></span></a>
        </div>
    </div>

    <div class="mtdss-modern-content">
        <?php if ( $active_tab === 'overview' ) : ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <!-- Card 1 -->
                <div style="border: 1px solid #e2e4e7; padding: 20px; border-radius: 6px;">
                    <h3 style="margin-top: 0; color: #646970; font-size: 13px; text-transform: uppercase;"><?php esc_html_e( 'Total Disk Usage', 'my-tiny-stats' ); ?></h3>
                    <p style="font-size: 24px; font-weight: 300; margin: 10px 0; color: #1d2327;"><?php echo esc_html( size_format( $used_space ) ); ?> <span style="font-size: 14px; color: #8c8f94;">/ <?php echo esc_html( size_format( $total_space ) ); ?></span></p>
                    <div style="height: 4px; background: #f0f0f1; width: 100%; border-radius: 2px;"><div style="height: 100%; background: #2271b1; width: <?php echo esc_attr( $used_pct ); ?>%;"></div></div>
                </div>
                <!-- Card 2 -->
                <div style="border: 1px solid #e2e4e7; padding: 20px; border-radius: 6px;">
                    <h3 style="margin-top: 0; color: #646970; font-size: 13px; text-transform: uppercase;"><?php esc_html_e( 'Database Size', 'my-tiny-stats' ); ?></h3>
                    <?php $db_total = array_sum( array_column( $db_stats, 'size' ) ); ?>
                    <p style="font-size: 24px; font-weight: 300; margin: 10px 0; color: #1d2327;"><?php echo esc_html( size_format( $db_total ) ); ?></p>
                    <p style="margin: 0; font-size: 13px; color: #2271b1; cursor: pointer;">View Tables &rarr;</p>
                </div>
                <!-- Card 3 -->
                <div style="border: 1px solid #e2e4e7; padding: 20px; border-radius: 6px; <?php echo $suspicious_count > 0 ? 'background: #fdfafa; border-color: #d63638;' : ''; ?>">
                    <h3 style="margin-top: 0; color: #646970; font-size: 13px; text-transform: uppercase;"><?php esc_html_e( 'Security Status', 'my-tiny-stats' ); ?></h3>
                    <p style="font-size: 24px; font-weight: 300; margin: 10px 0; color: <?php echo $suspicious_count > 0 ? '#d63638' : '#00a32a'; ?>">
                        <?php echo $suspicious_count > 0 ? esc_html( $suspicious_count ) . ' ' . esc_html__( 'Issues', 'my-tiny-stats' ) : esc_html__( 'Secure', 'my-tiny-stats' ); ?>
                    </p>
                    <?php if ( $suspicious_count > 0 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'tab', 'security', $base_url ) ); ?>" style="font-size: 13px; color: #d63638;"><?php esc_html_e( 'Review Risks', 'my-tiny-stats' ); ?> &rarr;</a>
                    <?php else : ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Overview: Mini Lists -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 20px;">
                
                <!-- Mini List: Large Files -->
                <div style="border: 1px solid #e2e4e7; background: #fff; border-radius: 6px; overflow: hidden;">
                    <div style="padding: 15px; border-bottom: 1px solid #f0f0f1; background: #fbfbfc; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 500;"><?php esc_html_e( 'Largest Files', 'my-tiny-stats' ); ?></h3>
                        <a href="<?php echo esc_url( add_query_arg( 'tab', 'files', $base_url ) ); ?>" style="font-size: 12px; text-decoration: none;"><?php esc_html_e( 'View All', 'my-tiny-stats' ); ?> &rarr;</a>
                    </div>
                    <table class="mtdss-modern-table" style="margin:0;">
                        <tbody>
                            <?php foreach ( array_slice( $scan_results['large_files'] ?? [], 0, 5 ) as $f ) : ?>
                            <tr>
                                <td style="padding: 10px 15px;">
                                    <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;" title="<?php echo esc_attr( $f['name'] ); ?>">
                                        <?php echo esc_html( $f['name'] ); ?>
                                    </div>
                                    <div style="color: #8c8f94; font-size: 11px;"><?php echo esc_html( basename( dirname( $f['path'] ) ) ); ?></div>
                                </td>
                                <td style="text-align: right; padding: 10px 15px; color: #646970;">
                                    <?php echo esc_html( size_format( $f['size'] ) ); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ( empty( $scan_results['large_files'] ) ) : ?>
                                <tr><td colspan="2" style="padding: 15px; text-align: center; color: #8c8f94;"><?php esc_html_e( 'No files found.', 'my-tiny-stats' ); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mini List: Recent Files -->
                <div style="border: 1px solid #e2e4e7; background: #fff; border-radius: 6px; overflow: hidden;">
                    <div style="padding: 15px; border-bottom: 1px solid #f0f0f1; background: #fbfbfc; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 500;"><?php esc_html_e( 'Recent Changes', 'my-tiny-stats' ); ?></h3>
                        <a href="<?php echo esc_url( add_query_arg( 'tab', 'recent', $base_url ) ); ?>" style="font-size: 12px; text-decoration: none;"><?php esc_html_e( 'View All', 'my-tiny-stats' ); ?> &rarr;</a>
                    </div>
                    <table class="mtdss-modern-table" style="margin:0;">
                        <tbody>
                            <?php foreach ( array_slice( $scan_results['recent_files'] ?? [], 0, 5 ) as $f ) : ?>
                            <tr>
                                <td style="padding: 10px 15px;">
                                    <div style="font-weight: 500; color: #2271b1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;" title="<?php echo esc_attr( $f['name'] ); ?>">
                                        <?php echo esc_html( $f['name'] ); ?>
                                    </div>
                                    <div style="color: #8c8f94; font-size: 11px;">
                                        <?php echo esc_html( date_i18n( get_option('date_format'), $f['date'] ) ); ?>
                                    </div>
                                </td>
                                <td style="text-align: right; padding: 10px 15px; color: #646970;">
                                    <?php echo esc_html( size_format( $f['size'] ) ); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ( empty( $scan_results['recent_files'] ) ) : ?>
                                <tr><td colspan="2" style="padding: 15px; text-align: center; color: #8c8f94;"><?php esc_html_e( 'No recent changes.', 'my-tiny-stats' ); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        
        <?php elseif ( $active_tab === 'recent' ) : ?>
            
            <?php
            $recent_stats = $scan_results['recent_stats'] ?? [];
            $recent_files = $scan_results['recent_files'] ?? [];
            $recent_days = (int) MTDSS_Settings::get_config( 'recent_days', 7 );
            ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <!-- translators: %d: number of days -->

                <h2 style="font-size: 18px; margin: 0; font-weight: 500; color: #1d2327;"><?php printf( esc_html__( 'Changes in last %d days', 'my-tiny-stats' ), $recent_days ); ?></h2>
            </div>

            <?php if ( ! empty( $recent_stats ) ) : ?>
            <div style="margin-bottom: 20px; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; background: #fff;">
                <h3 style="margin-top:0; font-size:14px; margin-bottom:10px;"><?php esc_html_e( 'Modified Folders', 'my-tiny-stats' ); ?></h3>
                <?php 
                foreach ( $recent_stats as $dir => $count ) {
                    $display_dir = str_replace( $upload_dir['basedir'], '', $dir );
                    if ( empty($display_dir) ) $display_dir = '/';
                    echo '<span class="mtdss-modern-badge mtdss-badge-blue" style="margin-right:8px; margin-bottom:8px;">' . esc_html( $display_dir ) . ': ' . intval( $count ) . '</span>';
                }
                ?>
            </div>
            <?php endif; ?>

            <table class="mtdss-modern-table">
                <thead>
                    <tr><th><?php esc_html_e( 'File', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Date', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Size', 'my-tiny-stats' ); ?></th></tr>
                </thead>
                <tbody>
                    <?php if(empty($recent_files)): ?><tr><td colspan="3"><?php esc_html_e( 'No recent files found.', 'my-tiny-stats' ); ?></td></tr><?php endif; ?>
                    <?php foreach ( array_slice( $recent_files, 0, 100 ) as $file ) : ?>
                        <tr>
                            <td><strong style="color:#2271b1;"><?php echo esc_html( $file['name'] ); ?></strong><div style="font-size:12px; color:#646970;"><?php echo esc_html( str_replace( $upload_dir['basedir'], '', $file['path'] ) ); ?></div></td>
                            <td><?php echo esc_html( date_i18n( 'Y-m-d H:i', $file['date'] ) ); ?></td>
                            <td><?php echo esc_html( size_format( $file['size'], 2 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ( $active_tab === 'files' ) : ?>
            <!-- Files Tab -->
            <div class="tablenav top" style="margin: 0 0 15px 0;">
                <form method="post" style="display:inline;"><input type="hidden" name="mtdss_refresh" value="1"><?php wp_nonce_field( 'mtdss_refresh_action', 'mtdss_refresh_nonce' ); ?><button class="button action"><?php esc_html_e( 'Refresh Data', 'my-tiny-stats' ); ?></button></form>
            </div>
            
            <table class="mtdss-modern-table">
                <thead><tr><th><?php esc_html_e( 'Filename', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Location', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Size', 'my-tiny-stats' ); ?></th><th style="text-align:right;"><?php esc_html_e( 'Date', 'my-tiny-stats' ); ?></th></tr></thead>
                <tbody>
                    <?php if(empty($scan_results['large_files'])): ?><tr><td colspan="4"><?php esc_html_e( 'No files found.', 'my-tiny-stats' ); ?></td></tr><?php endif; ?>
                    <?php foreach ( $scan_results['large_files'] ?? [] as $f ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $f['name'] ); ?></strong></td>
                            <td style="font-size: 12px; color: #8c8f94; word-break: break-all;"><?php echo esc_html( str_replace( $upload_dir['basedir'], '...', $f['path'] ) ); ?></td>
                            <td><?php echo esc_html( size_format( $f['size'] ) ); ?></td>
                            <td style="text-align:right; color: #8c8f94;"><?php echo esc_html( date_i18n( get_option('date_format'), $f['date'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ( $active_tab === 'security' ) : ?>
            <div style="background: #fff8e5; padding: 15px; border-left: 4px solid #f0b849; margin-bottom: 20px;">
                <p style="margin:0;"><strong><?php esc_html_e( 'Security Alert', 'my-tiny-stats' ); ?>:</strong> <?php esc_html_e( 'The following executable or hidden files were found in your uploads folder.', 'my-tiny-stats' ); ?></p>
            </div>
            
            <table class="mtdss-modern-table">
                <thead><tr><th><?php esc_html_e( 'File', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Reason', 'my-tiny-stats' ); ?></th><th><?php esc_html_e( 'Size', 'my-tiny-stats' ); ?></th></tr></thead>
                <tbody>
                    <?php if(empty($scan_results['suspicious_files'])): ?><tr><td colspan="3"><?php esc_html_e( 'System appears clean.', 'my-tiny-stats' ); ?></td></tr><?php endif; ?>
                    <?php foreach ( $scan_results['suspicious_files'] ?? [] as $f ) : ?>
                        <tr>
                            <td><strong style="color: #d63638;"><?php echo esc_html( $f['name'] ); ?></strong><br><span style="font-size: 11px; color: #8c8f94;"><?php echo esc_html( $f['path'] ); ?></span></td>
                            <td><span class="mtdss-modern-badge mtdss-badge-red"><?php echo esc_html( $f['reason'] ); ?></span></td>
                            <td><?php echo esc_html( size_format( $f['size'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        
        <?php endif; ?>
    </div>
</div>
