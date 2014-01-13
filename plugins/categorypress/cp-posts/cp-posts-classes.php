<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Posts_Post {
	var $id;
	var $parent;
	var $author;
	var $date_created;
	var $title;
	var $content;
	var $price;

	function __construct( $id = null ) {
		if ( !empty( $id ) )
			$this->populate( $id );
	}
	
	function populate( $id ) {
		global $wpdb, $cp;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$cp->posts->table_name} WHERE id = %d", $id );
		if ( $field = $wpdb->get_row( $sql ) ) {
			
			$this->id             = $field->id;
			$this->parent         = $field->parent;
			$this->author         = $field->author;
			$this->date_created   = $field->date_created;
			$this->title          = $field->title;
			$this->content        = $field->content;
			$this->price          = $filed->price;
		}
	}

	function save() {
		global $wpdb, $cp;

		if ( $this->exists() )
			$sql_cmd = $wpdb->prepare( "UPDATE {$cp->posts->table_name} SET parent = %d, author = %d, date_created = %d, title = %s, content = %s, price = %d WHERE id = %d", $this->parent, $this->author, cp_core_current_time(), $this->title, $this->content, $this->price, $this->id );
		else
			$sql_cmd = $wpdb->prepare( "INSERT INTO {$cp->posts->table_name} (parent, author, date_created, title, content, price) VALUES (%d, %d, %s, %s, %s, %d)", $this->parent, $this->author, cp_core_current_time(), $this->title, $this->content, $this->price );

		if ( false === $wpdb->query($sql_cmd) )
			return false;

		if ( empty( $this->id ) )
			$this->id = $wpdb->insert_id;

		wp_cache_delete( 'cp_posts_' . $this->id, 'cp' );

		return true;
	}

	function exists() {
		global $wpdb, $ppy;
		
		$retval = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$cp->posts->table_name} WHERE id = %d", $this->id ) );
		
		return $retval;
	}
	
	function delete( $id = null ) {
		global $wpdb, $ppy;
		
		if ( empty( $id ) )
			$id = $this->id;
		
		wp_cache_delete( 'cp_posts_' . $this->id, 'cp' );

		$sql_cmd = $wpdb->prepare( "DELETE FROM {$cp->post->table_name} WHERE id = %d", $id );
		if ( !$wpdb->query( $sql_cmd ) )
			return false;
		
		return true;
	}
	
	function delete_for_user( $author ) {
		global $wpdb, $ppy;
		
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$cp->posts->table_name} WHERE author = %d", $author ) );
	}
}