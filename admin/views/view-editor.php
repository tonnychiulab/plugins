<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Data Fetching
$scan_results = MTDSS_Data::scan_filesystem();
$db_stats = MTDSS_Data::get_database_usage( 100 );
$disk_stats = MTDSS_Data::get_disk_usage(); 

$file_limit = (int) MTDSS_Settings::get_config( 'file_limit', 10 );
$min_size = (int) MTDSS_Settings::get_config( 'min_file_size', 10 );
$recent_days = (int) MTDSS_Settings::get_config( 'recent_days', 7 );
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'files';

$base_url = admin_url( 'admin.php?page=my-tiny-stats&view_mode=editor' );
?>

<div class="mtdss-editor-container" style="display:flex; height: 600px; background: #1e1e1e; border-radius: 6px; overflow: hidden; font-family: 'Consolas', 'Monaco', monospace; margin-top:0;">
    
    <!-- Sidebar -->
    <div class="mtdss-editor-sidebar" style="width: 250px; background: #252526; color: #cccccc; font-size: 13px;">
        <div style="padding: 10px; font-weight: bold; text-transform: uppercase; font-size: 11px; margin-top: 10px; color: #6f7070;">Explorer</div>
        
        <!-- Files Section -->
        <div style="padding-left: 10px; cursor: pointer;" onclick="location.href='<?php echo esc_url( add_query_arg('tab', 'files', $base_url) ); ?>'">
            <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 12px; height: 12px; width: 12px; vertical-align: middle;"></span>
             LARGE_FILES
        </div>
        <?php if ($active_tab === 'files'): ?>
        <div style="margin-left: 20px; border-left: 1px solid #404040;">
            <?php foreach ( array_slice( $scan_results['large_files'] ?? [], 0, 5 ) as $f ) : ?>
                <div style="padding: 3px 10px; color: #d4d4d4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="dashicons dashicons-media-code" style="font-size: 12px; width: 12px; height: 12px; color: #dcdcaa;"></span> 
                    <?php echo esc_html( $f['name'] ); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Section -->
        <div style="padding-left: 10px; margin-top: 10px; cursor: pointer;" onclick="location.href='<?php echo esc_url( add_query_arg('tab', 'recent', $base_url) ); ?>'">
             <span class="dashicons dashicons-arrow-right" style="font-size: 12px; height: 12px; width: 12px; vertical-align: middle;"></span>
             RECENT_CHANGES
        </div>

        <!-- Security Section -->
        <div style="padding-left: 10px; margin-top: 10px; cursor: pointer;" onclick="location.href='<?php echo esc_url( add_query_arg('tab', 'security', $base_url) ); ?>'">
             <span class="dashicons dashicons-arrow-right" style="font-size: 12px; height: 12px; width: 12px; vertical-align: middle;"></span>
             SECURITY_STATUS
        </div>
    </div>

    <!-- Main Editor Area -->
    <div class="mtdss-editor-main" style="flex: 1; background: #1e1e1e; display: flex; flex-direction: column;">
        <!-- Tabs Header -->
        <div class="mtdss-editor-tabs" style="display: flex; background: #2d2d2d; height: 35px; border-bottom: 1px solid #000;">
            <?php
            $tabs = array( 
                'files' => 'large_files.json', 
                'recent' => 'recent_changes.log', 
                'security' => 'security_status.log', 
                'db' => 'database_dump.sql' 
            );
            foreach ($tabs as $k => $label) {
                $style = ($active_tab === $k) ? 'background: #1e1e1e; color: #fff; border-top: 1px solid #007acc;' : 'background: #2d2d2d; color: #969696;';
                echo '<a href="' . esc_url( add_query_arg( 'tab', $k, $base_url ) ) . '" style="padding: 8px 15px; text-decoration: none; font-size: 13px; display: inline-block; ' . $style . '">';
                echo esc_html( $label );
                if ( $active_tab === $k ) echo ' <span style="font-size:10px; margin-left:5px;">x</span>';
                echo '</a>';
            }
            ?>
        </div>

        <!-- Code Content -->
        <div class="mtdss-editor-content" style="flex: 1; padding: 20px; overflow-y: auto; color: #d4d4d4; font-size: 14px; line-height: 1.5;">
            
            <!-- LARGE FILES TAB -->
            <?php if ( $active_tab === 'files' ) : ?>
                <div style="margin-bottom: 20px;">
                    <span style="color: #6a9955;">// Config: Show Top <?php echo esc_html( $file_limit ); ?> Files > <?php echo esc_html( $min_size ); ?>MB</span><br>
                    <span style="color: #569cd6;">const</span> <span style="color: #4fc1ff;">largeFiles</span> = [
                </div>
                <?php foreach ( $scan_results['large_files'] ?? [] as $f ) : ?>
                <div style="padding-left: 20px;">
                    { <span style="color: #9cdcfe;">name</span>: <span style="color: #ce9178;">"<?php echo esc_html( $f['name'] ); ?>"</span>, <span style="color: #9cdcfe;">size</span>: <span style="color: #b5cea8;"><?php echo esc_html( size_format( $f['size'] ) ); ?></span>, <span style="color: #9cdcfe;">path</span>: <span style="color: #ce9178;">"<?php echo esc_html( $f['path'] ); ?>"</span> },
                </div>
                <?php endforeach; ?>
                <div style="margin-top: 10px;">];</div>
            
            <!-- RECENT TAB -->
            <?php elseif ( $active_tab === 'recent' ) : ?>
                <div style="margin-bottom: 20px;">
                    <span style="color: #6a9955;">// Recent Changes (Last <?php echo esc_html( $recent_days ); ?> Days)</span><br>
                    <span style="color: #c586c0;">tail</span> -f /var/log/recent_changes.log
                </div>
                <?php 
                $recent_files = $scan_results['recent_files'] ?? [];
                if ( empty( $recent_files ) ) {
                    echo '<div style="color: #808080;">No changes detected.</div>';
                } else {
                    foreach ( array_slice( $recent_files, 0, 50 ) as $f ) {
                        echo '<div><span style="color: #569cd6;">' . esc_html( date_i18n( 'Y-m-d H:i:s', $f['date'] ) ) . '</span> - ' . esc_html( $f['name'] ) . ' <span style="color: #6a9955;">' . esc_html( size_format( $f['size'] ) ) . '</span></div>';
                    }
                }
                ?>
            
            <!-- SECURITY TAB -->
            <?php elseif ( $active_tab === 'security' ) : ?>
                <div style="margin-bottom: 20px;">
                    <span style="color: #d16969;">[CRITICAL]</span> Security Scan Results...
                </div>
                <?php 
                $suspicious = $scan_results['suspicious_files'] ?? [];
                if ( empty( $suspicious ) ) {
                    echo '<div style="color: #6a9955;">> System Scan Complete. No threats found.</div>';
                } else {
                    foreach ( $suspicious as $f ) {
                        echo '<div style="margin-bottom: 5px;">';
                        echo '<span style="color: #f44747;">[THREAT]</span> ' . esc_html( $f['name'] ) . ' <span style="color: #808080;">(' . esc_html( $f['reason'] ) . ')</span><br>';
                        echo '<span style="color: #808080; padding-left: 20px;">Path: ' . esc_html( $f['path'] ) . '</span>';
                        echo '</div>';
                    }
                }
                ?>

            <!-- DB TAB -->
            <?php elseif ( $active_tab === 'db' ) : ?>
                <div style="margin-bottom: 20px;">
                    <span style="color: #569cd6;">SELECT</span> * <span style="color: #569cd6;">FROM</span> wp_tables <span style="color: #569cd6;">ORDER BY</span> size <span style="color: #569cd6;">DESC</span>;
                </div>
                <table style="width: 100%; text-align: left;">
                <?php foreach ( $db_stats as $t ) : ?>
                    <tr>
                        <td style="color: #9cdcfe;"><?php echo esc_html( $t['name'] ); ?></td>
                        <td style="color: #b5cea8;"><?php echo esc_html( size_format( $t['size'] ) ); ?></td>
                        <td style="color: #ce9178;"><?php echo number_format( $t['rows'] ); ?> rows</td>
                    </tr>
                <?php endforeach; ?>
                </table>

            <?php endif; ?>
        </div>

        <!-- Status Bar -->
        <div style="background: #007acc; color: #fff; padding: 5px 10px; font-size: 12px; display: flex; justify-content: space-between;">
             <div>Master *</div>
             <div>Ln 1, Col 1 UTF-8 PHP</div>
        </div>
    </div>
</div>
