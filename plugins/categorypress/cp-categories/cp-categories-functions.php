<?php

/**
 * CategoryPress Categories Functions
 *
 * Functions are where all the magic happens in CategoryPress.
 *
 * @package CategoryPress
 * @subpackage CategoriesFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_categories_get_category( $args = '' ) {
	if ( empty( $args ) )
		return false;
	
	$defaults = array(
			'id'           => '',
			'slug'         => ''
			);
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	$cache_key = 'CP_Category_' . $id . '_' . $slug ;
	
	if ( !$category = wp_cache_get( $cache_key, 'cp' ) ) {
		
		if ( empty( $slug ) )
			$category = new CP_Category( $id );
		else
			$category = CP_Category::get_by_slug( $slug );
		
		wp_cache_set( $cache_key, $category, 'cp' );
	}
	
	return apply_filters( 'categories_get_category', $category );
}

/**
 * Get a category slug by its ID
 *
 * @param int $category_id The numeric ID of the category
 * @return string The category's slug
 */
function cp_categories_get_slug( $category_id ) {
	$category = cp_categories_get_category( array( 'id' => $category_id ) );
	return !empty( $category->slug ) ? $category->slug : '';
}


function cp_categories_create_category( $args = '' ) {
	extract( $args );
	
	/**
	 * Possible parameters (pass as assoc array):
	 *	'category_id'
	 *	'parent_id'
	 *	'name'
	 *	'description'
	 *	'slug'
	 *	'date_created'
	 */
	
	if ( !empty( $category_id ) )
		$category = categories_get_category( array( 'category_id' => $category_id ) );
	else
		$category = new CP_Category;
	
	if ( !empty( $parent_id ) )
		$category->parent_id = $parent_id;
	else
		$category->parent_id = 0;
	
	if ( isset( $name ) )
		$category->name = $name;
	
	if ( isset( $description ) )
		$category->description = $description;
	
	if ( isset( $slug ) && categories_check_slug( $slug ) )
		$category->slug = $slug;
	
	if ( isset( $date_created ) )
		$category->date_created = $date_created;
	
	if ( !$category->save() )
		return false;
	
	do_action( 'categories_created_category', $category->id, $category );
	
	return $category->id;
}

function cp_categories_update_categorymeta( $category_id, $meta_key, $meta_value ) {
	global $wpdb, $cp;
	
	if ( !is_numeric( $category_id ) )
		return false;
	
	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );
	
	if ( is_string( $meta_value ) )
		$meta_value = stripslashes( esc_sql( $meta_value ) );
	
	$meta_value = maybe_serialize( $meta_value );
	
	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $cp->categories->table_name_categorymeta . " WHERE category_id = %d AND meta_key = %s", $category_id, $meta_key ) );
	
	if ( !$cur )
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $cp->categories->table_name_categorymeta . " ( category_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $category_id, $meta_key, $meta_value ) );
	else if ( $cur->meta_value != $meta_value )
		$wpdb->query( $wpdb->prepare( "UPDATE " . $cp->categories->table_name_categorymeta . " SET meta_value = %s WHERE category_id = %d AND meta_key = %s", $meta_value, $category_id, $meta_key ) );
	else
		return false;
	
	// Update the cached object and recache
	wp_cache_set( 'CP_Categorymeta_' . $category_id . '_' . $meta_key, $meta_value, 'cp' );
	
	return true;
}

function cp_categories_get_permalink( $slug, $type = false, $ignore_crumb = false ) {
	
	switch( $type ) {
		
		case CP_CategoryType::$NORMAL:
		case CP_CategoryType::$BRAND:
			
			if ( $ignore_crumb == false ) {
				
				$cur_area = cp_current_area_slug();
				
				if ( !empty( $cur_area ) )
					$cur_area .= '/';
				
				$cur_price = cp_current_price_slug();
				if ( !empty( $cur_price ) )
					$cur_price .= '/';
				
				$link = cp_get_root_domain() . '/' . CP_POSTS_SLUG . '/'. cp_current_city_slug() . '/' . $slug . '/' . $cur_area . $cur_price;
			} else {
				
				if ( cp_current_city_slug() == $slug )
					$link = cp_get_root_domain() . '/' . CP_POSTS_SLUG . '/' . $slug;
				else
					$link = cp_get_root_domain() . '/' . CP_POSTS_SLUG . '/' . cp_current_city_slug() . '/' . $slug;
			}
			break;
		
		case CP_CategoryType::$AREA:
			
			$cur_price = cp_current_price_slug();
			if ( !empty( $cur_price ) )
				$cur_price .= '/';
			
			$link = cp_get_root_domain() . '/' . CP_POSTS_SLUG . '/'. cp_current_city_slug() . '/' . cp_current_category_slug() . '/' . $slug . '/' . $cur_price;
			break;
		
		case CP_CategoryType::$PRICE:
			
			$cur_area = cp_current_area_slug();
			
			if ( !empty( $cur_area ) )
				$cur_area .= '/';
			
			$link = cp_get_root_domain() . '/' . CP_POSTS_SLUG . '/'. cp_current_city_slug() . '/' . cp_current_category_slug() . '/' . $cur_area . $slug . '/';
			break;
		
	}		
	
	return apply_filters_ref_array( 'cp_categories_get_permalink', array( $link, &$slug, &$type ) );
}

function cp_categories_get_parent( $args = '' ) {
	if ( empty( $args ) )
		return false;
	
	$defaults = array(
			'child_id'           => false,
			'slug'               => false
			);
	
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	return CP_Category::get_parent( $child_id, $slug );
}

function cp_categories_set_parent( $parent_id, $child_id ) {
	global $wpdb, $cp;
	
	$exists = (bool) $wpdb->get_var( $wpdb->prepare( "SELECT child_id FROM " . $cp->categories->table_name_c_in_c . " WHERE parent_id = %d AND child_id = %d", $parent_id, $child_id ) );
	
	if ( !$exists )
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $cp->categories->table_name_c_in_c . " ( parent_id, child_id ) VALUES ( %d, %d )", $parent_id, $child_id ) );
}

function cp_categories_get_current_id() {
	$slug = cp_current_category_slug();
	return cp_categories_get_id( $slug );
}

function cp_categories_get_id( $slug ) {
	return (int) CP_Category::category_exists( $slug );
}

function cp_categories_get_types() {
	$types = array();
	
	$types[CP_CategoryType::$NORMAL] = 'normal';
	$types[CP_CategoryType::$BRAND] = 'brand';
	$types[CP_CategoryType::$AREA] = 'area';
	
	return $types;
}

function cp_categories_get_categories( $args = '' ) {
	
	$categories = CP_Category::get( $args );
	
	return apply_filters_ref_array( 'cp_categories_get_categories', array( &$categories, &$r ) );
}

function cp_categories_get_total_category_count() {
	
	if ( !$count = wp_cache_get( 'cp_total_category_count', 'cp' ) ) {
		$count = CP_Category::get_total_category_count();
		wp_cache_set( 'cp_total_category_count', $count, 'cp' );
	}
	
	return $count;
}

function cp_categories_get_user_categories( $user_id = 0, $pag_num = 0, $pag_page = 0 ) {
	
	if ( empty( $user_id ) )
		$user_id = bp_displayed_user_id();
	
	return BP_Groups_Member::get_category_ids( $user_id, $pag_num, $pag_page );
}

function cp_categories_total_categories_for_user( $user_id = 0 ) {
	
	if ( empty( $user_id ) )
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
	
	if ( !$count = wp_cache_get( 'cp_total_categories_for_user_' . $user_id, 'cp' ) ) {
		$count = BP_Groups_Member::total_category_count( $user_id );
		wp_cache_set( 'cp_total_categories_for_user_' . $user_id, $count, 'cp' );
	}
	
	return $count;
}

function cp_categories_check_category_exists( $slug, $parent_slug = '' ) {
	$category_exists = false;
	
	if ( empty( $slug ) ) 
		return false;
	
	$category_exists = (bool) CP_Category::category_exists( $slug );
	
	if ( $category_exists && !empty( $parent_slug ) ) {
		$category_id = CP_Category::get_id_from_slug( $slug );
		$category  = CP_Category::get_parent( $category_id );
		
		$category_exists = $category->slug == $parent_slug;
	}
	
	return $category_exists;
}

function cp_do_404( $redirect = 'remove_canonical_direct' ) {
	global $wp_query;
	
	do_action( 'cp_do_404', $redirect );
	
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	
	if ( 'remove_canonical_direct' == $redirect )
		remove_action( 'template_redirect', 'redirect_canonical' );
}

function cp_categories_head() {
	if ( cp_is_posts_component() ) {
		$cur_category_slug = cp_current_category_slug();
		$cur_category = cp_categories_get_category( array( 'slug' => $cur_category_slug ) );
		
		echo '<meta name="keywords" content="' . $cur_category->name . '"/>' . PHP_EOL;
		echo '<meta name="description" content="' . $cur_category->desc . '"/>' . PHP_EOL;
	}
}
add_action ( 'cp_head', 'cp_categories_head' );

function cp_categories_title( $title, $sep, $seplocation ) {
	if ( cp_is_posts_component() ) {
		$cur_category_slug = cp_current_category_slug();
		$cur_category = cp_categories_get_category( array( 'slug' => $cur_category_slug ) );
		
		return $cur_category->name . $sep;
	}
	return $title;
}
add_filter( 'wp_title', 'cp_categories_title', 10, 3 );