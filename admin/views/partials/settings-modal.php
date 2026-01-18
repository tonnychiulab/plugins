<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$limit = (int) MTDSS_Settings::get_config( 'file_limit', 10 );
$min_size = (int) MTDSS_Settings::get_config( 'min_file_size', 10 );
$recent_days = (int) MTDSS_Settings::get_config( 'recent_days', 7 );
$exts = MTDSS_Settings::get_config( 'suspicious_extensions', 'php,exe,sh' );
?>

<div id="mtdss-settings-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999;">
    <div style="background:#fff; width:500px; margin: 100px auto; padding:20px; border-radius:5px; box-shadow:0 4px 10px rgba(0,0,0,0.1); position:relative;">
        <span class="dashicons dashicons-no-alt" style="float:right; cursor:pointer;" onclick="document.getElementById('mtdss-settings-modal').style.display='none';"></span>
        <h2 style="margin-top:0;"><?php esc_html_e( 'Global Configuration', 'my-tiny-stats' ); ?></h2>
        <p><?php esc_html_e( 'Manage your scanning preferences and security thresholds.', 'my-tiny-stats' ); ?></p>
        
        <form method="post">
            <?php wp_nonce_field( 'mtdss_save_settings_action', 'mtdss_save_settings_nonce' ); ?>
            <input type="hidden" name="mtdss_save_settings" value="1">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Min Size (MB):', 'my-tiny-stats' ); ?></label></th>
                    <td><input type="number" name="mtdss_min_size" value="<?php echo esc_attr( $min_size ); ?>" class="small-text"> MB</td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'List Limit:', 'my-tiny-stats' ); ?></label></th>
                    <td><input type="number" name="mtdss_limit" value="<?php echo esc_attr( $limit ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Recent (Days):', 'my-tiny-stats' ); ?></label></th>
                    <td><input type="number" name="mtdss_recent_days" value="<?php echo esc_attr( $recent_days ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e( 'Suspicious Exts:', 'my-tiny-stats' ); ?></label></th>
                    <td>
                        <textarea name="mtdss_extensions" rows="3" class="large-text code"><?php echo esc_textarea( $exts ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Separate with commas (e.g. php, exe)', 'my-tiny-stats' ); ?></p>
                    </td>
                </tr>
            </table>

            <div style="text-align:right; margin-top:20px;">
                <button class="button button-primary"><?php esc_html_e( 'Save Changes', 'my-tiny-stats' ); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle function for Modal (can be called from Admin Bar or other buttons)
function mtdss_toggle_settings() {
    var el = document.getElementById('mtdss-settings-modal');
    el.style.display = (el.style.display == 'none') ? 'block' : 'none';
}
// Listen to Admin Bar link
jQuery(document).ready(function($){
    $('#wp-admin-bar-mtdss_shortcut').click(function(e){
        e.preventDefault();
        mtdss_toggle_settings();
    });
});
</script>
