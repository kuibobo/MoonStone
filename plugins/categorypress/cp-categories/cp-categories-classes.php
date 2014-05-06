<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
class CP_CategoryType {
	public static $NORMAL   = 0;
	public static $BRAND    = 1;
	public static $AREA     = 2;
	public static $PRICE    = 3;
}

class CP_Category {
	var $id;
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
			$sql_cmd = $wpdb->prepare( "UPDATE {$cp->categories->table_name} SET name = %s, description = %s, slug = %s, type = %d, date_created = %s WHERE id = %d", $this->name, $this->description, $this->slug, $this->type, cp_core_current_time(), $this->id );
		else
			$sql_cmd = $wpdb->prepare( "INSERT INTO {$cp->categories->table_name} (name, description, slug, type, date_created) VALUES (%d, %s, %s, %s, %d, %s)", $this->name, $this->description, $this->slug, $this->type, cp_core_current_time() );

		if ( false === $wpdb->query($sql_cmd) )
			return false;

		if ( empty( $this->id ) )
			$this->id = $wpdb->insert_id;

		wp_cache_delete( 'CP_Category_' . $this->id, 'cp' );

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
		return CP_Category::category_exists( $slug, $type, $parent_id );
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
	
	public static function get_by_slug( $slug ) {
		global $wpdb, $cp;
		
		if ( empty( $slug ) )
			return false;
		
		$category = false;
		$sql = $wpdb->prepare( "SELECT * FROM {$cp->categories->table_name} WHERE slug = %s", $slug );
		if ( $field = $wpdb->get_row( $sql ) ) {
			$category = new CP_Category();
			
			$category->id                = $field->id;
			$category->name              = $field->name;
			$category->description       = $field->description;
			$category->slug              = $field->slug;
			$category->type              = $filed->type;
			$category->date_created      = $field->date_created;
		}
		
		return $category;
	}
	
	public static function category_exists( $slug ) {
		global $wpdb, $cp;
				
		if ( empty( $slug ) )
			return false;
			
		$retval = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$cp->categories->table_name} WHERE slug = %s", $slug ) );
		
		return $retval;
	}
	
	public static function get_parent( $child_id, $slug ) {
		global $wpdb, $cp;
		
		$where_args = false;
		
		if ( !empty( $child_id ) )
			$where_args[] = $wpdb->prepare( "child_id = %d", $child_id );
			
		if ( !empty( $slug ) )
			$where_args[] = $wpdb->prepare( "slug = %s", $slug );
			
		if ( !empty( $where_args ) )
			$where_sql = 'WHERE ' . join( ' AND ', $where_args );
		else
			return false;
			
		$retval = $wpdb->get_row( "SELECT * FROM {$cp->categories->table_name} WHERE id = (SELECT parent_id FROM {$cp->categories->table_name_c_in_c} {$where_sql})" );
		
		return $retval;
	}
	
	/**
	 * Convert the 'type' parameter to 'order' and 'orderby'.
	 *
	 * @since BuddyPress (1.8.0)
	 * @access protected
	 *
	 * @param string $type The 'type' shorthand param.
	 * @return array {
	 *	@type string $order SQL-friendly order string.
	 *	@type string $orderby SQL-friendly orderby column name.
	 * }
	 */
	protected static function convert_type_to_order_orderby( $type = '' ) {			
		return array( 'order' => 'DESC', 'orderby' => $type );
	}
	
	/**
	 * Convert the 'orderby' param into a proper SQL term/column.
	 *
	 * @since BuddyPress (1.8.0)
	 * @access protected
	 *
	 * @param string $orderby Orderby term as passed to get().
	 * @return string $order_by_term SQL-friendly orderby term.
	 */
	protected static function convert_orderby_to_order_by_term( $orderby ) {
		$order_by_term = '';
		
		switch ( $orderby ) {
			case 'date_created' :
			default :
				$order_by_term = 'c.date_created';
				break;
							
			case 'name' :
				$order_by_term = 'c.name';
				break;
			
			case 'random' :
				$order_by_term = 'rand()';
				break;
		}
		
		return $order_by_term;
	}

	public static function get( $args = array() ) {
		global $wpdb, $cp;
					
		$defaults = array(
				'type'            => CP_CategoryType::$NORMAL,    // active, newest, alphabetical, random, popular, most-forum-topics or most-forum-posts
				'order'           => 'DESC',   // 'ASC' or 'DESC'
				'orderby'         => 'date_created', // date_created, last_activity, total_member_count, name, random
				'parent_id'       => 0,
				'per_page'        => 20,       // The number of results to return per page
				'page'            => 1        // The page to return if limiting per page
				);
		
		$r = wp_parse_args( $args, $defaults );
		
		$sql       = array();
		$total_sql = array();
		
		$sql['select'] = "SELECT *";
		$sql['from']   = " FROM {$cp->categories->table_name} c";
		$sql['category_join'] = " JOIN {$cp->categories->table_name_c_in_c} cc ON cc.child_id = c.id";
		
		if ( !empty( $r['parent_id'] ) )
			$sql['parent_where'] =  $wpdb->prepare( "WHERE cc.parent_id = %d", $r['parent_id'] );
			
		$sql['type_where'] =  $wpdb->prepare( " AND c.type = %d", $r['type'] );
		
		/** Order/orderby ********************************************/
		
		$order   = $r['order'];
		$orderby = $r['orderby'];

	
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
		
		// Get paginated results
		$paged_categories_sql = join( ' ', (array) $sql );
		$paged_categories     = $wpdb->get_results( $paged_categories_sql );
		
		$sql['select'] = "SELECT COUNT(DISTINCT c.id) ";
		$total_categories_sql = join( ' ', (array) $sql );
		$total_categories     = $wpdb->get_var( $total_categories_sql );
		
		unset( $sql );
		
		return array( 'categories' => $paged_categories, 'total' => $total_categories );
	}

}