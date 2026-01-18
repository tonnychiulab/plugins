<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Ensure Meta Boxes are registered
if ( ! did_action( 'add_meta_boxes' ) ) {
    do_action( 'add_meta_boxes', 'toplevel_page_my-tiny-stats', null );
}

// Manually register if needed (since we are late in execution flow inside render)
// But ideally this should be in Controller.
// For now, let's define the callbacks here or assume Controller registered them.
// To keep View clean, we'll implement the widgets AS FUNCTIONS in this file or imported.

// Let's define the widget rendering functions locally or in a helper if they are complex.
// Since we are refactoring, let's keep it simple: Render content directly or use do_meta_boxes.
// But do_meta_boxes requires add_meta_box to be called earlier. 
// A better approach for this View file is to just RENDER the grid if we don't want to over-engineer the WP Meta Box API for a custom page again.
// However, the user liked drag-and-drop. So we MUST use do_meta_boxes.

// We need to make sure `add_meta_box` was called.
// I will update MTDSS_Admin to call `add_meta_boxes` hook. 
// For this file, we assume `do_meta_boxes` will work.
?>

<div class="metabox-holder" id="dashboard-widgets">
    <div class="postbox-container" style="width:49%; display:inline-block; vertical-align:top;">
        <?php do_meta_boxes( 'toplevel_page_my-tiny-stats', 'normal', null ); ?>
    </div>
    <div class="postbox-container" style="width:49%; display:inline-block; vertical-align:top; margin-left:1%;">
        <?php do_meta_boxes( 'toplevel_page_my-tiny-stats', 'side', null ); ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    postboxes.add_postbox_toggles( 'toplevel_page_my-tiny-stats' );
});
</script>
