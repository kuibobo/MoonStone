<?php

if ( !defined( 'ABSPATH' ) ) exit;

function cp_get_major_wp_version() {
	global $wp_version;
	
	return (float) $wp_version;
}

if ( !is_multisite() ) {
	global $wpdb;
	
	$wpdb->base_prefix = $wpdb->prefix;
	$wpdb->blogid      = CP_ROOT_BLOG;
	
	if ( !function_exists( 'get_blog_option' ) ) {
		function get_blog_option( $blog_id, $option_name, $default = false ) {
			return get_option( $option_name, $default );
		}
	}
	
	if ( !function_exists( 'update_blog_option' ) ) {
		function update_blog_option( $blog_id, $option_name, $value ) {
			return update_option( $option_name, $value );
		}
	}
	
	if ( !function_exists( 'delete_blog_option' ) ) {
		function delete_blog_option( $blog_id, $option_name ) {
			return delete_option( $option_name );
		}
	}
}

if ( !function_exists( 'utf8_to_gb2312' ) ) {
	function utf8_to_gb2312($raw) { 
		$str = ''; 
		
		if( $raw < 0x80) {
			$str .= $raw; 
		} elseif( $raw < 0x800) { 
			$str .= chr( 0xC0 | $raw >> 6 ); 
			$str .= chr( 0x80 | $raw & 0x3F ); 
		}elseif( $raw < 0x10000) { 
			$str .= chr( 0xE0 | $raw >> 12); 
			$str .= chr( 0x80 | $raw >> 6 & 0x3F ); 
			$str .= chr( 0x80 | $raw & 0x3F ); 
		} elseif( $raw < 0x200000) { 
			$str .= chr( 0xF0 | $raw >> 18 ); 
			$str .= chr( 0x80 | $raw >> 12 & 0x3F ); 
			$str .= chr( 0x80 | $raw >> 6 & 0x3F ); 
			$str .= chr( 0x80 | $raw & 0x3F ); 
		} 
		
		return iconv('UTF-8', 'GB2312', $str); 
	} 
}

if ( !function_exists( 'ends_with' ) ) {
	function ends_with( $haystack, $needle ) {
		return $needle === "" || substr( $haystack, -strlen( $needle ) ) === $needle;
	}
}

if ( !function_exists( 'starts_with' ) ) {
	function starts_with( $haystack, $needle ) {
		//return $needle === "" || strpos($haystack, $needle) === 0;
		return substr( $haystack, 0, strlen($needle) ) === $needle;
	}
}