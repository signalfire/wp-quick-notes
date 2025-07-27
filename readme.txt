=== Signalfire Quick Notes ===
Contributors: signalfire
Tags: dashboard, notes, admin, quick-notes, team-communication
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a simple "Quick Notes" widget to the WordPress dashboard for administrators and editors to leave short notes for team communication.

== Description ==

Signalfire Quick Notes adds a convenient dashboard widget that allows administrators and editors to leave short notes for team members. Perfect for internal communication, reminders, and quick updates that need to be visible to all team members when they log into the WordPress admin.

**Features:**

* Simple dashboard widget for quick note-taking
* Auto-save functionality - notes are saved as you type
* Manual save button for immediate saving
* Shows who last updated the notes and when
* Supports basic HTML formatting
* Restricted to users with edit_posts capability
* Clean, intuitive interface that fits seamlessly with WordPress admin

**Use Cases:**

* Team communication and updates
* Important reminders for content editors
* Quick notes about site maintenance
* Temporary announcements for admin users
* Collaborative note-taking for editorial teams

The plugin is lightweight, secure, and follows WordPress coding standards. It stores notes in the WordPress database and includes proper nonce verification and user capability checks.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/signalfire-quick-notes` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The Quick Notes widget will automatically appear on your dashboard.
4. Start adding notes! They'll be saved automatically as you type, or click the Save button for immediate saving.

== Frequently Asked Questions ==

= Who can see and edit the quick notes? =

Only users with the 'edit_posts' capability can see and edit the notes. This typically includes Administrators, Editors, and Authors, but not Subscribers or Contributors.

= Are the notes saved automatically? =

Yes! The plugin includes auto-save functionality that saves your notes 2 seconds after you stop typing. You can also click the "Save Notes" button to save immediately.

= Can I use HTML in the notes? =

Yes, basic HTML formatting is supported. The content is filtered through WordPress's wp_kses_post() function to ensure safe HTML.

= Will the notes be visible to website visitors? =

No, the notes are only visible in the WordPress admin dashboard to users with appropriate permissions.

= What happens if I deactivate the plugin? =

The notes data will remain in your database. If you reactivate the plugin, your notes will still be there. To completely remove all data, you'll need to uninstall the plugin.

== Screenshots ==

1. Quick Notes dashboard widget showing the note-taking interface
2. Auto-save status indicators and last updated information

== Changelog ==

= 1.0.0 =
* Initial release
* Dashboard widget for quick note-taking
* Auto-save and manual save functionality
* User tracking for notes updates
* HTML content support with security filtering

== Upgrade Notice ==

= 1.0.0 =
Initial release of Signalfire Quick Notes. Install to add team communication capabilities to your WordPress dashboard.