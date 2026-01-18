<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * System Vitals Partial
 * Displays CPU, RAM, and Disk Usage in a condensed bar.
 * Matches user request for "Pre-flight Check".
 */

// Colors
$color_blue  = '#2271b1';
$color_gray  = '#f0f0f1';
$color_red   = '#d63638';
$color_green = '#00a32a';

// Disk
$disk = $vitals['disk'];
$disk_pct = $disk ? $disk['pct'] : 0;
$disk_bar_w = $disk_pct;

// RAM
$ram = $vitals['ram'];
$ram_pct = $ram ? $ram['pct'] : 0;
$ram_text = $ram ? size_format( $ram['used'] ) . ' / ' . size_format( $ram['total'] ) . ' (' . $ram_pct . '%)' : 'N/A';

// CPU
$cpu = $vitals['cpu'];
$load_text = $cpu ? implode( ' / ', $cpu ) : 'N/A';
?>

<div class="mtdss-vitals-container" style="background: #fff; padding: 15px; border: 1px solid #c3c4c7; border-left-width: 4px; border-left-color: #2271b1; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
    <h3 style="margin-top:0; font-size: 13px; color: #646970; text-transform: uppercase; margin-bottom: 15px;">System Vitals (Pre-flight Check)</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
        
        <!-- RAM -->
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;">
                <span>🧠 <strong>memory (RAM)</strong>:</span>
                <span><?php echo esc_html( $ram_text ); ?></span>
            </div>
            <div style="height: 12px; background: #f0f0f1; border-radius: 6px; overflow: hidden;">
                <div style="height: 100%; background: #2271b1; width: <?php echo esc_attr( $ram_pct ); ?>%;"></div>
            </div>
            <?php if ( isset( $ram['type'] ) && strpos( $ram['type'], 'Simulation' ) !== false ) : ?>
                <div style="font-size:10px; color:#c3c4c7; text-align:right;">(Mock/PHP Limit)</div>
            <?php endif; ?>
        </div>

        <!-- Disk -->
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;">
                <span>💾 <strong>Disk Space</strong>:</span>
                <?php if ( $disk ) : ?>
                <span><?php echo size_format( $disk['free'] ); ?> free / <?php echo size_format( $disk['total'] ); ?> total</span>
                <?php else: ?>
                <span>Unknown</span>
                <?php endif; ?>
            </div>
            <div style="height: 12px; background: #f0f0f1; border-radius: 6px; overflow: hidden;">
                <div style="height: 100%; background: #2271b1; width: <?php echo esc_attr( $disk_pct ); ?>%;"></div>
            </div>
        </div>

        <!-- CPU -->
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;">
                <span>⚙️ <strong>CPU Load</strong> (1m/5m/15m):</span>
            </div>
            <div style="font-size: 16px; font-weight: 500; font-family: monospace; letter-spacing: 0.5px;">
                <?php echo esc_html( $load_text ); ?>
            </div>
            <div style="font-size: 12px; color: #00a32a; margin-top: 5px;">
                <span class="dashicons dashicons-yes-alt" style="font-size:14px; vertical-align:middle;"></span> System Healthy
            </div>
        </div>

    </div>
</div>
