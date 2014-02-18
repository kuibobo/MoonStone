<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Return the name of the current component.
 *
 * @return string Component name.
 */
function cp_current_component() {
	global $bp;
	$current_component = !empty( $bp->current_component ) ? $bp->current_component : false;
	return apply_filters( 'cp_current_component', $current_component );
}

function cp_is_posts_component() {
	return cp_is_current_component( 'posts' );
}

function cp_is_categories_component() {
	return cp_is_current_component( 'categories' );
}

function cp_is_current_component( $component ) {
	global $cp;
	
	$is_current_component = false;
	
	if ( empty( $component ) )
		return false;
		
	if ( !empty( $cp->current_component ) ) {
		
		if ( $cp->current_component == $component )
			$is_current_component = true;
	}
	
	return $is_current_component;
}

function cp_current_categories() {
	global $cp;
	
	if ( empty( $cp->current_categories ) )
		return '';
	
	return $cp->current_categories;
}

function cp_current_category() {
	global $cp;
	
	if ( empty( $cp->current_categories ) )
		return '';
		
	return $cp->current_categories[ count( $cp->current_categories ) - 1 ];
}

function cp_current_post() {
	global $cp;
	
	$current_post = !empty( $cp->current_post ) ? $cp->current_post : '';
	return $current_post;
}

/**
 * Is this a BuddyPress component?
 *
 * You can tell if a page is displaying BP content by whether the
 * current_component has been defined.
 *
 * Generally, we can just check to see that there's no current component.
 * The one exception is single user home tabs, where $bp->current_component
 * is unset. Thus the addition of the bp_is_user() check.
 *
 * @since BuddyPress (1.7.0)
 *
 * @return bool True if it's a BuddyPress page, false otherwise.
 */
function is_categorypress() {
	$retval = (bool) ( cp_current_component() || cp_is_user() );
	
	return apply_filters( 'is_categorypress', $retval );
}

/**
 * Output the search slug.
 *
 * @since BuddyPress (1.5.0)
 *
 * @uses bp_get_search_slug()
 */
function cp_search_slug() {
	echo cp_get_search_slug();
}
	/**
	 * Return the search slug.
	 *
	 * @since BuddyPress (1.5.0)
	 *
	 * @return string The search slug. Default: 'search'.
	 */
	function cp_get_search_slug() {
		return apply_filters( 'cp_get_search_slug', CP_SEARCH_SLUG );
	}
	
	
/**
 * Is the current page a user page?
 *
 * Will return true anytime there is a displayed user.
 *
 * @return True if the current page is a user page.
 */
function cp_is_user() {
	if ( cp_displayed_user_id() )
		return true;
	
	return false;
}

/**
 * Get the ID of the currently displayed user.
 *
 * @uses apply_filters() Filter 'bp_displayed_user_id' to change this value.
 *
 * @return int ID of the currently displayed user.
 */
function cp_displayed_user_id() {
	//$bp = buddypress();
	//$id = !empty( $bp->displayed_user->id ) ? $bp->displayed_user->id : 0;
	
	//return (int) apply_filters( 'cp_displayed_user_id', $id );
	return -99;
}