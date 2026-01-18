=== My Tiny Stats ===
Contributors: Tonny
Tags: disk space, server stats, storage, dashboard
Requires at least: 5.8
Tested up to: 6.9
Stable Tag: 1.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display server disk space statistics with a visual progress bar in the admin menu.

== Description ==

**My Tiny Stats** (formerly My Tiny Disk Space Stats) provides a lightweight, single-screen dashboard to monitor your WordPress server's health.
It goes beyond checkng disk space—it analyzes **Database Tables**, **System Directories**, and **File Types** to help you understand exactly what is consuming your resources.Total space in GB.
*   Color-coded warnings when disk usage exceeds 90%.
*   Native WordPress admin interface integration.

== Installation ==

1. Upload the `my-tiny-disk-space-stats.php` file to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the stats via the new "Disk Space Stats" menu item in the admin sidebar.

== Frequently Asked Questions ==

= Does this work on shared hosting? =
Yes, as long as the PHP `disk_total_space` and `disk_free_space` functions are enabled by your host.

= How often is the data updated? =
The data is real-time and updates every time you refresh the page.

== Screenshots ==

1. The main statistics view with progress bar.

== Changelog ==

= 1.3 =
* New: Rebranded to "My Tiny Stats" with unified UI.
* New: Added "System Directory Usage" to analyze Plugins, Themes, Uploads, and Core sizes.
* New: Added "Database Footprint" analysis (Top tables).
* Enhancement: Unified "Max Items to Show" setting for both files and tables (Default: 10).
* Enhancement: Improved security with strict escaping and cache protection.
* Fix: Addressed Plugin Check warnings and improved i18n support.

= 1.2 =
*   Rename: Renamed plugin to My Tiny Disk Space Stats.
*   Security: Added direct access prevention and escaping.
*   I18n: Added translation support.
*   UI: Modernized UI with progress bar and card layout.
*   Fix: Refactored inline styles to proper enqueue methods.

= 1.0 =
*   Initial release.
