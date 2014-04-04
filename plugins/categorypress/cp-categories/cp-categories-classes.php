<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Categories_CategoryType {
	public static $MASTER   = 0;
	public static $SLAVE    = 1;
	public static $BRAND    = 2;
}

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
	
	public static function get_parent_slug( $slug ) {
		global $wpdb, $cp;
		
		if ( empty( $slug ) )
			return false;
		
		$retval = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$cp->categories->table_name} WHERE id = (SELECT parent FROM {$cp->categories->table_name} WHERE slug = %s)", $slug ) );
		
		return $retval;
	}

	public static function get( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
				'type'            => null,
				'orderby'         => 'date_created',
				'order'           => 'DESC',
				'per_page'        => null,
				'page'            => null,
				'parent_id'       => 0,
				'search_terms'    => false,
				'meta_query'      => false,
				'include'         => false,
				'populate_extras' => true,
				'exclude'         => false,
				'show_hidden'     => false,
				);
		
		$r = wp_parse_args( $args, $defaults );
		
		$sql       = array();
		$total_sql = array();
		
		$sql['select'] = "SELECT *";
		$sql['from']   = " FROM {$cp->categories->table_name} c";
		
		if ( ! empty( $r['parent_id'] ) ) 
			$sql['parent_where'] =  $wpdb->prepare( " AND c.parent = %d", $r['parent_id'] );
			
		$sql['where'] = "";
		
		/** Order/orderby ********************************************/
		
		$order   = $r['order'];
		$orderby = $r['orderby'];

		// If a 'type' parameter was passed, parse it and overwrite
		// 'order' and 'orderby' params passed to the function
		if (  ! empty( $r['type'] ) ) {
			$order_orderby = self::convert_type_to_order_orderby( $r['type'] );
			
			// If an invalid type is passed, $order_orderby will be
			// an array with empty values. In this case, we stick
			// with the default values of $order and $orderby
			if ( ! empty( $order_orderby['order'] ) ) {
				$order = $order_orderby['order'];
			}
			
			if ( ! empty( $order_orderby['orderby'] ) ) {
				$orderby = $order_orderby['orderby'];
			}
		}
		
		// Sanitize 'order'
		$order = bp_esc_sql_order( $order );
		
		// Convert 'orderby' into the proper ORDER BY term
		$orderby = self::convert_orderby_to_order_by_term( $orderby );
		
		// Random order is a special case
		if ( 'rand()' === $orderby ) {
			$sql[] = "ORDER BY rand()";
		} else {
			$sql[] = "ORDER BY {$orderby} {$order}";
		}
		
		if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) ) {
			$sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page']), intval( $r['per_page'] ) );
		}
	}

}