<?php
/**
 * Uninstall script for Signalfire Quick Notes
 *
 * This file is called when the plugin is uninstalled via the WordPress admin.
 * It removes all plugin data from the database.
 *
 * @package SignalfireQuickNotes
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'sqn_notes_data' );

// For multisite installations, delete options from all sites
if ( is_multisite() ) {
	$sites = get_sites();
	$original_blog_id = get_current_blog_id();
	
	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );
		delete_option( 'sqn_notes_data' );
	}
	
	switch_to_blog( $original_blog_id );
}