<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Categories_Category {
	var $id;
	var $parent_id;
	var $name;
	var $description;
	var $slug;
	var $type;
	var $date_created;
	var $sub_category;

	function __construct( $id = null ) {
		if ( !empty( $id ) )
			$this->populate( $id );
	}
	
	function populate( $id ) {
		global $wpdb, $cp;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$cp->categories->table_name} WHERE id = %d", $id );
		if ( $field = $wpdb->get_row( $sql ) ) {
			
			$this->id                = $field->id;
			$this->parent_id         = $field->parent_id;
			$this->name              = $field->name;
			$this->description       = $field->description;
			$this->slug              = $field->slug;
			$this->type              = $filed->type;
			$this->date_created      = $field->date_created;
		}
	}

	function save() {
		global $wpdb, $cp;

		if ( $this->exists() )
			$sql_cmd = $wpdb->prepare( "UPDATE {$cp->categories->table_name} SET parent = %d, name = %s, description = %s, slug = %s, type = %d, date_created = %s WHERE id = %d", $this->parent_id, $this->name, $this->description, $this->slug, $this->type, cp_core_current_time(), $this->id );
		else
			$sql_cmd = $wpdb->prepare( "INSERT INTO {$cp->categories->table_name} (parent, name, description, slug, type, date_created) VALUES (%d, %s, %s, %s, %d, %s)", $this->parent_id, $this->name, $this->description, $this->slug, $this->type, cp_core_current_time() );

		if ( false === $wpdb->query($sql_cmd) )
			return false;

		if ( empty( $this->id ) )
			$this->id = $wpdb->insert_id;

		wp_cache_delete( 'cp_categories_category_' . $this->id, 'cp' );

		return true;
	}

	function exists() {
		global $wpdb, $cp;
		
		if ( empty ( $this->id ) )
			$retval = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$cp->categories->table_name} WHERE name = %s", $this->name ) );
		else
			$retval = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$cp->categories->table_name} WHERE id = %d", $this->id ) );
		
		return $retval;
	}

	function get_id_from_slug( $slug, $type = 0, $parent_id = 0 ) {
		return CP_Categories_Category::category_exists( $slug, $type, $parent_id );
	}
	
	function get_by_name( $name ) {
		global $wpdb, $cp;
		
		if ( empty( $name ) )
			return false;
		
		$category = false;
		$sql = $wpdb->prepare( "SELECT * FROM {$cp->categories->table_name} WHERE name = %s", $name );
		if ( $field = $wpdb->get_row( $sql ) ) {
			$category = new CP_Categories_Category();
			
			$category->id                = $field->id;
			$category->parent_id         = $field->parent_id;
			$category->name              = $field->name;
			$category->description       = $field->description;
			$category->slug              = $field->slug;
			$category->type              = $filed->type;
			$category->date_created      = $field->date_created;
		}
		
		return $category;
	}

	function get_total_category_count() {
		global $wpdb, $cp;

		return $wpdb->get_var( "SELECT COUNT(id) FROM {$cp->categories->table_name}" );
	}
	
	/** Static Methods ****************************************************/
	//function category_exists( $slug, $type = 0, $parent_id = 0 ) {
	//	global $wpdb, $cp;
	//
	//	if ( empty( $slug ) )
	//		return false;
	//
	//	return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$cp->categories->table_name} WHERE parent AND slug = %s AND type = %d", $parent_id, $slug, $type ) );
	//}
	
	public static function category_exists( $slug ) {
		global $wpdb, $cp;
				
		if ( empty( $slug ) )
			return false;
			
		$retval = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$cp->categories->table_name} WHERE slug = %s", $slug ) );
		
		return $retval;
	}
}