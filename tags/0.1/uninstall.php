<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key='_sidebar_name'");
?>