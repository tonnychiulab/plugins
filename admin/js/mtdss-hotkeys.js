/**
 * My Tiny Stats - Hotkeys & Shortcuts
 * 
 * Global:
 *  Alt + 1 : Dashboard View
 *  Alt + 2 : Modern View
 *  Alt + 3 : Editor View
 *  Alt + S : Global Settings
 * 
 * Navigation (Context Aware):
 *  1 : Jump to Disk/Overview
 *  2 : Jump to Large Files
 *  3 : Jump to Recent Files
 *  4 : Jump to Security
 */

jQuery(document).ready(function ($) {

    // Helper: Get Base URL without parameters
    function getBaseUrl() {
        return window.location.href.split('?')[0] + '?page=my-tiny-stats';
    }

    $(document).keydown(function (e) {
        // Ignore if typing in input fields
        if ($(e.target).is('input, textarea')) return;

        // View Switching (Alt + 1/2/3)
        if (e.altKey && e.key === '1') {
            window.location.href = getBaseUrl() + '&view_mode=dashboard';
        }
        if (e.altKey && e.key === '2') {
            window.location.href = getBaseUrl() + '&view_mode=modern';
        }
        if (e.altKey && e.key === '3') {
            window.location.href = getBaseUrl() + '&view_mode=editor';
        }

        // Settings (Alt + S)
        if (e.altKey && (e.key === 's' || e.key === 'S')) {
            if (typeof mtdss_toggle_settings === 'function') {
                mtdss_toggle_settings();
                e.preventDefault();
            }
        }

        // Navigation (1, 2, 3, 4) - No modifier keys needed
        if (!e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey) {
            var handled = false;

            // Determine current view
            var urlParams = new URLSearchParams(window.location.search);
            // Can't rely on URL params due to clean URL. 
            // Check DOM classes
            var isDashboard = $('.metabox-holder').length > 0;
            var isModern = $('.mtdss-modern-container').length > 0;
            var isEditor = $('.mtdss-editor-container').length > 0;

            if (e.key === '1') { // Disk / Overview
                handled = true;
                if (isDashboard) $('html, body').animate({ scrollTop: $('#mtdss_disk_usage').offset().top - 50 }, 200);
                if (isModern) window.location.href = getBaseUrl() + '&view_mode=modern&tab=overview';
                if (isEditor) window.location.href = getBaseUrl() + '&view_mode=editor&tab=files'; // Close enough
            }
            if (e.key === '2') { // Large Files
                handled = true;
                if (isDashboard) $('html, body').animate({ scrollTop: $('#mtdss_large_files').offset().top - 50 }, 200);
                if (isModern) window.location.href = getBaseUrl() + '&view_mode=modern&tab=files';
                if (isEditor) window.location.href = getBaseUrl() + '&view_mode=editor&tab=files';
            }
            if (e.key === '3') { // Recent
                handled = true;
                if (isDashboard) $('html, body').animate({ scrollTop: $('#mtdss_recent_files').offset().top - 50 }, 200);
                if (isModern) window.location.href = getBaseUrl() + '&view_mode=modern&tab=recent';
                if (isEditor) window.location.href = getBaseUrl() + '&view_mode=editor&tab=recent';
            }
            if (e.key === '4') { // Security
                handled = true;
                if (isDashboard) $('html, body').animate({ scrollTop: $('#mtdss_security').offset().top - 50 }, 200);
                if (isModern) window.location.href = getBaseUrl() + '&view_mode=modern&tab=security';
                if (isEditor) window.location.href = getBaseUrl() + '&view_mode=editor&tab=security';
            }

            if (handled) e.preventDefault();
        }
    });

    // Add Hint UI (Corner toast)
    var hkHint = $('<div id="mtdss-hk-hint" style="position:fixed; bottom:20px; right:20px; background:#333; color:#fff; padding:10px 15px; border-radius:4px; font-size:12px; opacity:0; transition:opacity 0.3s; z-index:9999;">Tips: Alt+1/2/3 to Switch Views</div>').appendTo('body');

    // Show hint briefly on load
    setTimeout(function () { hkHint.css('opacity', 0.8); }, 1000);
    setTimeout(function () { hkHint.css('opacity', 0); }, 5000); // Hide after 4s
});
