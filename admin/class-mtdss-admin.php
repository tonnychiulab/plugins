<?php
/**
 * My Tiny Stats - Admin Controller
 * 
 * Handles Admin Menu, View Routing, and Requests.
 * 
 * @package My_Tiny_Stats
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MTDSS_Admin {

    public function init() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
        add_action( 'admin_init', array( $this, 'check_db_schema' ) );
        add_action( 'admin_init', array( $this, 'handle_view_mode_switch' ) );
        add_action( 'add_meta_boxes', array( $this, 'register_dashboard_widgets' ) );
    }

    public function check_db_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mtdss_config';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            mtdss_activate(); // Re-run activation logic
        }
    }

    /**
     * Handle View Mode Switch (Redirect for Clean URL)
     */
    public function handle_view_mode_switch() {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'my-tiny-stats' && isset( $_GET['view_mode'] ) ) {
            $new_mode = sanitize_key( $_GET['view_mode'] );
            if ( in_array( $new_mode, array( 'dashboard', 'modern', 'editor' ) ) ) {
                update_user_meta( get_current_user_id(), 'mtdss_view_mode', $new_mode );
            }
            // Redirect to clean URL
            wp_safe_redirect( remove_query_arg( 'view_mode' ) );
            exit;
        }
    }

    public function add_menu() {
        add_menu_page(
            __( 'My Tiny Stats', 'my-tiny-stats' ),
            __( 'My Tiny Stats', 'my-tiny-stats' ),
            'manage_options',
            'my-tiny-stats',
            array( $this, 'render_page' ),
            'dashicons-chart-pie',
            100
        );
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== 'toplevel_page_my-tiny-stats' ) {
            return;
        }
        // Enqueue native WP scripts for dashboard widgets
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
        
        // Custom Assets
        wp_enqueue_script( 'mtdss-hotkeys', MTDSS_PLUGIN_URL . 'admin/js/mtdss-hotkeys.js', array('jquery'), MTDSS_VERSION, true );
    }

    public function admin_bar_menu( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $wp_admin_bar->add_node( array(
            'id'    => 'mtdss_shortcut',
            'title' => '<span class="ab-icon dashicons dashicons-chart-pie"></span> ' . __( 'My Tiny Stats', 'my-tiny-stats' ),
            'href'  => admin_url( 'admin.php?page=my-tiny-stats' ),
            'meta'  => array( 'class' => 'mtdss-admin-bar-item' )
        ));
    }

    public function render_page() {
        // Handle Logic
        $this->handle_requests();

        // Get View Mode
        $view_mode = get_user_meta( get_current_user_id(), 'mtdss_view_mode', true );
        if ( ! in_array( $view_mode, array( 'dashboard', 'editor', 'modern' ) ) ) $view_mode = 'dashboard';

        // Load View
        $base_url = admin_url( 'admin.php?page=my-tiny-stats' );
        
        // Common Data
        $vitals = MTDSS_Data::get_system_vitals();

        // Render Page Wrapper
        ?>
        <div class="wrap mtdss-wrap">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h1 class="wp-heading-inline" style="margin:0;">
                    <span class="dashicons dashicons-chart-pie" style="font-size: 28px; width: 28px; height: 28px; margin-right: 5px;"></span>
                    <?php esc_html_e( 'My Tiny Stats', 'my-tiny-stats' ); ?>
                </h1>
                
                <div style="display:flex; gap:10px; align-items:center;">
                    <!-- View Switcher -->
                    <div class="mtdss-view-switcher">
                         <?php 
                         $modes = array(
                             'dashboard' => array( 'icon' => 'dashicons-grid-view', 'label' => __( 'Dashboard', 'my-tiny-stats' ) ),
                             'modern'    => array( 'icon' => 'dashicons-laptop',    'label' => __( 'Modern Lite', 'my-tiny-stats' ) ),
                             'editor'    => array( 'icon' => 'dashicons-editor-code', 'label' => __( 'Editor Mode', 'my-tiny-stats' ) ),
                         );
                         foreach($modes as $key => $m) {
                             $active = ($view_mode === $key) ? 'color: #2271b1; background: #f0f0f1;' : 'color: #50575e;';
                             echo '<a href="' . esc_url( add_query_arg('view_mode', $key, $base_url) ) . '" class="mtdss-view-btn tips" title="' . esc_attr($m['label']) . '" style="text-decoration:none; padding: 5px 8px; border-radius: 4px; ' . $active . '"><span class="dashicons ' . $m['icon'] . '"></span></a>';
                         }
                         ?>
                    </div>
                    
                    <!-- Settings Gear -->
                    <a href="#" class="tips" title="<?php esc_attr_e( 'Global Settings', 'my-tiny-stats' ); ?>" onclick="mtdss_toggle_settings(); return false;" style="text-decoration:none; color:#50575e; padding:5px; margin-left:5px;">
                        <span class="dashicons dashicons-admin-generic" style="font-size: 20px; width: 20px; height: 20px; vertical-align: middle;"></span>
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php settings_errors( 'mtdss_messages' ); ?>

            <!-- System Vitals Bar (Shared) -->
            <?php include_once plugin_dir_path( __FILE__ ) . 'views/partials/system-vitals.php'; ?>
            
            <!-- Global Settings Modal -->
            <?php include_once plugin_dir_path( __FILE__ ) . 'views/partials/settings-modal.php'; ?>

            <!-- Main Content View -->
            <?php 
            switch ( $view_mode ) {
                case 'editor':
                    include_once plugin_dir_path( __FILE__ ) . 'views/view-editor.php';
                    break;
                case 'modern':
                    include_once plugin_dir_path( __FILE__ ) . 'views/view-modern.php';
                    break;
                case 'dashboard':
                default:
                    // Dashboard needs special meta box setup logic
                    include_once plugin_dir_path( __FILE__ ) . 'views/view-dashboard.php';
                    break;
            } 
            ?>
        </div>
        <?php
    }

    private function handle_requests() {
        // Refresh
        if ( isset( $_POST['mtdss_refresh'] ) && check_admin_referer( 'mtdss_refresh_action', 'mtdss_refresh_nonce' ) && current_user_can( 'manage_options' ) ) {
            MTDSS_Settings::clear_transients();
            add_settings_error( 'mtdss_messages', 'mtdss_message', __( 'Stats refreshed successfully.', 'my-tiny-stats' ), 'updated' );
        }

        // Save Settings
        if ( isset( $_POST['mtdss_save_settings'] ) && check_admin_referer( 'mtdss_save_settings_action', 'mtdss_save_settings_nonce' ) && current_user_can( 'manage_options' ) ) {
            $limit = isset( $_POST['mtdss_limit'] ) ? intval( $_POST['mtdss_limit'] ) : 10;
            $min_size = isset( $_POST['mtdss_min_size'] ) ? intval( $_POST['mtdss_min_size'] ) : 10;
            $recent_days = isset( $_POST['mtdss_recent_days'] ) ? intval( $_POST['mtdss_recent_days'] ) : 7;
            $exts = isset( $_POST['mtdss_extensions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mtdss_extensions'] ) ) : '';
            
            if ( $limit < 5 ) $limit = 5;
            if ( $limit > 100 ) $limit = 100;
            if ( $min_size < 1 ) $min_size = 1;
            if ( $recent_days < 1 ) $recent_days = 1;

            MTDSS_Settings::update_config( 'file_limit', (string)$limit );
            MTDSS_Settings::update_config( 'min_file_size', (string)$min_size );
            MTDSS_Settings::update_config( 'recent_days', (string)$recent_days );
            MTDSS_Settings::update_config( 'suspicious_extensions', $exts );
            
            MTDSS_Settings::clear_transients();
            add_settings_error( 'mtdss_messages', 'mtdss_message', __( 'Settings saved.', 'my-tiny-stats' ), 'updated' );
        }
    }


    /**
     * Register Dashboard Widgets
     */
    public function register_dashboard_widgets() {
        add_meta_box( 'mtdss_disk_usage', __( 'Total Disk Usage', 'my-tiny-stats' ), array( $this, 'widget_disk_usage' ), 'toplevel_page_my-tiny-stats', 'normal' );
        add_meta_box( 'mtdss_db_usage', __( 'Database Size', 'my-tiny-stats' ), array( $this, 'widget_db_usage' ), 'toplevel_page_my-tiny-stats', 'side' );
        add_meta_box( 'mtdss_large_files', __( 'Large Files', 'my-tiny-stats' ), array( $this, 'widget_large_files' ), 'toplevel_page_my-tiny-stats', 'normal' );
        add_meta_box( 'mtdss_recent_files', __( 'Recent Files', 'my-tiny-stats' ), array( $this, 'widget_recent_files' ), 'toplevel_page_my-tiny-stats', 'side' );
        add_meta_box( 'mtdss_security', __( 'Security Status', 'my-tiny-stats' ), array( $this, 'widget_security_status' ), 'toplevel_page_my-tiny-stats', 'side' );
    }

    // Widget: Disk Usage
    public function widget_disk_usage() {
        $stats = MTDSS_Data::get_disk_usage();
        if ( ! $stats ) { echo 'N/A'; return; }
        ?>
        <div style="text-align:center; padding:10px;">
            <h2 style="font-size:32px; margin:0; font-weight:300;"><?php echo size_format($stats['used']); ?> <span style="font-size:14px; color:#888;">/ <?php echo size_format($stats['total']); ?></span></h2>
            <div style="background:#f0f0f1; border-radius:4px; margin-top:10px; height:20px; text-align:center; color:#fff; line-height:20px; overflow:hidden;">
                 <div style="background:#2271b1; width:<?php echo $stats['pct']; ?>%; height:100%;"><?php echo $stats['pct']; ?>%</div>
            </div>
        </div>
        <?php
    }

    // Widget: DB Usage
    public function widget_db_usage() {
        $stats = MTDSS_Data::get_database_usage( 5 );
        $total = array_sum( array_column( $stats, 'size' ) );
        echo '<div style="margin-bottom:10px; font-size:14px;"><strong>' . __( 'Total Size:', 'my-tiny-stats' ) . '</strong> ' . size_format($total) . '</div>';
        echo '<table class="widefat striped">';
        foreach($stats as $t) {
            echo '<tr><td>' . esc_html($t['name']) . '</td><td style="text-align:right;">' . size_format($t['size']) . '</td></tr>';
        }
        echo '</table>';
    }

    // Widget: Large Files
    public function widget_large_files() {
        $data = MTDSS_Data::scan_filesystem();
        $files = $data['large_files'] ?? [];
        if ( empty($files) ) { echo '<p>' . __( 'No files found.', 'my-tiny-stats' ) . '</p>'; return; }
        
        echo '<table class="widefat striped">';
        foreach($files as $f) {
            echo '<tr>';
            echo '<td style="word-break:break-all;"><strong>' . esc_html($f['name']) . '</strong><br><span style="color:#888;">' . esc_html($f['path']) . '</span></td>';
            echo '<td style="width:80px; text-align:right;">' . size_format($f['size']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<form method="post" style="margin-top:10px;"><input type="hidden" name="mtdss_refresh" value="1">';
        wp_nonce_field( 'mtdss_refresh_action', 'mtdss_refresh_nonce' );
        echo '<button class="button action">' . __( 'Refresh Data', 'my-tiny-stats' ) . '</button></form>';
    }

    // Widget: Recent Files
    public function widget_recent_files() {
        $data = MTDSS_Data::scan_filesystem();
        $recent = $data['recent_files'] ?? [];
        $stats = $data['recent_stats'] ?? [];
        $days = (int) MTDSS_Settings::get_config( 'recent_days', 7 );
        
        echo '<p><strong>' . sprintf( __( 'Files modified in the last %d days:', 'my-tiny-stats' ), $days ) . '</strong></p>';
        
        if ( !empty($stats) ) {
            echo '<div style="margin-bottom:10px;">';
            foreach($stats as $dir => $count) {
                 $d = str_replace( wp_upload_dir()['basedir'], '', $dir );
                 echo '<span class="mtdss-modern-badge mtdss-badge-blue" style="margin-right:5px; margin-bottom:5px; display:inline-block; border:1px solid #cce5ff; background:#e6f7ff; padding:2px 6px; border-radius:3px;">' . esc_html( $d ? $d : '/' ) . ': ' . intval($count) . '</span>';
            }
            echo '</div>';
        }
        
        if ( empty($recent) ) {
            echo '<p>' . __( 'No recent changes.', 'my-tiny-stats' ) . '</p>';
        } else {
            echo '<div style="max-height:250px; overflow-y:auto;"><ul style="list-style:none; margin:0; padding:0;">';
            foreach(array_slice($recent, 0, 20) as $f) {
                echo '<li style="border-bottom:1px solid #f0f0f1; padding:5px 0;">';
                echo '<span style="color:#2271b1;">' . esc_html($f['name']) . '</span> <span style="color:#888; font-size:12px;">(' . size_format($f['size']) . ')</span>';
                echo '<div style="font-size:11px; color:#aaa;">' . date('Y-m-d H:i', $f['date']) . '</div>';
                echo '</li>';
            }
            echo '</ul></div>';
        }
    }

    // Widget: Security
    public function widget_security_status() {
        $data = MTDSS_Data::scan_filesystem();
        $suspicious = $data['suspicious_files'] ?? [];
        
        if ( empty($suspicious) ) {
            echo '<div style="color:#00a32a; font-weight:500; padding:10px 0;"><span class="dashicons dashicons-yes-alt"></span> ' . __( 'System appears clean.', 'my-tiny-stats' ) . '</div>';
        } else {
            echo '<div style="background:#fff8e5; border-left:4px solid #f0b849; padding:8px 12px; margin-bottom:10px;">';
            echo sprintf( __( '%d potential issues found.', 'my-tiny-stats' ), count($suspicious) );
            echo '</div>';
            echo '<ul style="margin:0; padding:0; list-style:none;">';
            foreach($suspicious as $f) {
                 echo '<li style="margin-bottom:8px; border-bottom:1px solid #f0f0f1; padding-bottom:5px;">';
                 echo '<strong style="color:#d63638;">' . esc_html($f['name']) . '</strong>';
                 echo '<div style="font-size:11px; background:#fee2e2; color:#991b1b; display:inline-block; padding:2px 4px; border-radius:3px; margin-left:5px;">' . esc_html($f['reason']) . '</div>';
                 echo '<div style="font-size:11px; color:#888;">' . esc_html($f['path']) . '</div>';
                 echo '</li>';
            }
            echo '</ul>';
        }
    }
}
