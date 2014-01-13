<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_set_charset() {
	global $wpdb;
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	return !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : '';
}

function cp_core_install_posts() {

	$sql               = array();
	$charset_collate = cp_core_set_charset();
	$cp_prefix        = cp_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$cp_prefix}cp_posts (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				parent bigint(20) NOT NULL,
		  		author bigint(20) NOT NULL,
		  		title varchar(10) NOT NULL,
				content longtext NOT NULL,
				price tinyint(1) NOT NULL DEFAULT '1',
				date_created datetime NOT NULL,
			    KEY parent (parent),
			    KEY author (author)
		 	   ) {$charset_collate};";
				
	$sql[] = "CREATE TABLE {$cp_prefix}cp_postmeta (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			post_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			KEY post_id (post_id),
			KEY meta_key (meta_key)
			) {$charset_collate};";
	
	dbDelta( $sql );
}

function cp_core_install_categories() {

	$sql             = array();
	$charset_collate = cp_core_set_charset();
	$cp_prefix       = cp_core_get_table_prefix();

	$sql[] = "CREATE TABLE {$cp_prefix}cp_categories (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				parent bigint(20) NOT NULL,
		  		name varchar(100) NOT NULL,
				description varchar(4000) NOT NULL DEFAULT '',
				slug varchar(64) NOT NULL DEFAULT '',
				type tinyint(1) NOT NULL DEFAULT '1',
				date_created datetime NOT NULL,
			    KEY parent (parent)
		 	   ) {$charset_collate};";

	dbDelta( $sql );
}

function cp_core_install( $active_components = false ) {

	if ( empty( $active_components ) )
		$active_components = apply_filters( 'cp_active_components', cp_get_option( 'cp-active-components' ) );

	if ( !empty( $active_components['post'] ) )
		cp_core_install_posts();

	if ( !empty( $active_components['category'] ) )
		cp_core_install_categories();
}