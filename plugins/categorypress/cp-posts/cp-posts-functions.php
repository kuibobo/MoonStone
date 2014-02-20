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

function cp_post_add( $args = '' ) {
	global $user_ID;

	$defaults = array(
		'id'                  => false,
		'post_parent'         => false,
		'post_author'         => false,
		'post_date'           => '',
		'post_title'          => '',
		'post_content'        => ''

	);
	$params = wp_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	if ( isset($post_parent) )
		$post_parent = (int) $post_parent;
	else
		$post_parent = 0;

	if ( empty($post_author) )
		$post_author = $user_ID;

	// Setup post to be added
	$post                    = new CP_Posts_Post( $id );
	$post->post_parent       = $post_parent;
	$post->post_author       = $post_author;
	$post->post_date         = $post_date;
	$post->post_title        = $post_title;
	$post->post_content      = $post_content;

	if ( !$post->save() )
		return false;

	do_action( 'cp_post_add', $params );

	return $post->id;
}

function cp_posts_get_post( $args = '' ) {
	$defaults = array(
		'post_id' => false
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	$cache_key = 'cp_posts_post_' . $post_id ;

	if ( !$post = wp_cache_get( $cache_key, 'cp' ) ) {
		$post = new CP_Posts_Post( $post_id );
		wp_cache_set( $cache_key, $post, 'cp' );
	}

	return apply_filters( 'posts_get_post', $post );
}

function cp_posts_update_postmeta( $post_id, $meta_key, $meta_value ) {
	global $wpdb, $cp;

	if ( !is_numeric( $post_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string( $meta_value ) )
		$meta_value = stripslashes( esc_sql( $meta_value ) );

	$meta_value = maybe_serialize( $meta_value );

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key ) );

	if ( !$cur )
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $cp->posts->table_name_postmeta . " ( post_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $post_id, $meta_key, $meta_value ) );
	else if ( $cur->meta_value != $meta_value )
		$wpdb->query( $wpdb->prepare( "UPDATE " . $cp->posts->table_name_postmeta . " SET meta_value = %s WHERE post_id = %d AND meta_key = %s", $meta_value, $post_id, $meta_key ) );
	else
		return false;

	// Update the cached object and recache
	wp_cache_set( 'cp_posts_postsmeta_' . $post_id . '_' . $meta_key, $meta_value, 'cp' );

	return true;
}

function cp_posts_delete_postmeta( $post_id, $meta_key = false, $meta_value = false ) {
	global $wpdb, $cp;

	if ( !is_numeric( $post_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_array( $meta_value ) || is_object( $meta_value ) )
		$meta_value = serialize($meta_value);

	$meta_value = trim( $meta_value );

	if ( !$meta_key )
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d", $post_id ) );
	else if ( $meta_value )
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d AND meta_key = %s AND meta_value = %s", $post_id, $meta_key, $meta_value ) );
	else
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key ) );

	// Delete the cached object
	wp_cache_delete( 'cp_posts_postmeta_' . $post_id . '_' . $meta_key, 'cp' );

	return true;
}

function cp_posts_get_postmeta( $post_id, $meta_key = '') {
	global $wpdb, $cp;

	$post_id = (int) $post_id;

	if ( !$post_id )
		return false;

	if ( !empty($meta_key) ) {
		$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

		$metas = wp_cache_get( 'cp_posts_postmeta_' . $post_id . '_' . $meta_key, 'cp' );
		if ( false === $metas ) {
			$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key ) );
			wp_cache_set( 'cp_posts_postmeta_' . $post_id . '_' . $meta_key, $metas, 'cp' );
		}
	} else {
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $cp->posts->table_name_postmeta . " WHERE post_id = %d", $post_id ) );
	}

	if ( empty( $metas ) ) {
		if ( empty( $meta_key ) )
			return array();
		else
			return '';
	}

	$metas = array_map( 'maybe_unserialize', (array) $metas );

	if ( 1 == count( $metas ) )
		return $metas[0];
	else
		return $metas;
}

/**
 * Get a collection of posts, based on the parameters passed
 *
 * @uses apply_filters_ref_array() Filter 'cp_posts_get_posts' to modify return value
 * @uses CP_Posts_Post::get()
 * @param array $args See inline documentation for details
 * @return array
 */
function cp_posts_get_posts( $args = '' ) {

	$defaults = array(
			'type'            => false,    // active, newest, alphabetical, random, popular, most-forum-topics or most-forum-posts
			'order'           => 'DESC',   // 'ASC' or 'DESC'
			'orderby'         => 'date_created', // date_created, last_activity, total_member_count, name, random
			'user_id'         => false,    // Pass a user_id to limit to only posts that this user is a member of
			'parent'          => false,
			'include'         => false,    // Only include these specific posts (group_ids)
			'exclude'         => false,    // Do not include these specific post (group_ids)
			'search_terms'    => false,    // Limit to posts that match these search terms
			'meta_query'      => false,    // Filter by postmeta. See WP_Meta_Query for syntax
			'show_hidden'     => false,    // Show hidden posts to non-admins
			'per_page'        => 20,       // The number of results to return per page
			'page'            => 1,        // The page to return if limiting per page
			'populate_extras' => true,     // Fetch meta such as is_banned and is_member
	);

	$r = wp_parse_args( $args, $defaults );

	$groups = CP_Posts_Post::get( array(
			'type'            => $r['type'],
			'user_id'         => $r['user_id'],
			'parent'          => $r['parent'],
			'include'         => $r['include'],
			'exclude'         => $r['exclude'],
			'search_terms'    => $r['search_terms'],
			'meta_query'      => $r['meta_query'],
			'show_hidden'     => $r['show_hidden'],
			'per_page'        => $r['per_page'],
			'page'            => $r['page'],
			'populate_extras' => $r['populate_extras'],
			'order'           => $r['order'],
			'orderby'         => $r['orderby'],
	) );

	return apply_filters_ref_array( 'cp_posts_get_posts', array( &$groups, &$r ) );
}