jQuery(document).ready(function ($) {

    // --- Tabs Logic ---
    // --- Tabs Logic ---
    $('.nav-tab-wrapper a').on('click', function (e) {
        e.preventDefault();
        // Remove active class from all tabs and contents
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $('.mtdss-tab-content').removeClass('active');

        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');

        // Show target content
        var target = $(this).attr('href');
        $(target).addClass('active');

        // Update URL hash (optional, but good for UX)
        // history.replaceState(null, null, target);
        // Note: We use query param ?tab= for pagination, so hash might conflict or be redundant.
        // But for visual switching, this is fine.
    });

    // Handle "View Details" link in notices
    $('.mtdss-switch-tab').on('click', function (e) {
        e.preventDefault();
        var targetTab = $(this).attr('href');

        // Trigger click on actual tab
        $('.nav-tab-wrapper a[href="' + targetTab + '"]').click();
    });

    // --- Admin Pointers Logic ---
    // Check if configuration object exists
    if (typeof mtdss_vars !== 'undefined' && mtdss_vars.pointer_content) {

        var pointerTarget = '#toplevel_page_my-tiny-disk-space-stats';

        $(pointerTarget).pointer({
            content: mtdss_vars.pointer_content,
            position: {
                edge: 'left',
                align: 'center'
            },
            close: function () {
                // Send AJAX request to save dismissal
                $.post(ajaxurl, {
                    pointer: mtdss_vars.pointer_id,
                    action: 'dismiss-wp-pointer'
                });
            }
        }).pointer('open');
    }

});
