<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    api_consumer
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$option_name = 'api_consumer';

delete_option( $option_name );

// For site options in Multisite.
delete_site_option( $option_name );
