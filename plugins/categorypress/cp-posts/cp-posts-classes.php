<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Post {
	var $id;
	var $parent;
	var $author;
	var $thumb;
	var $date_created;
	var $name;
	var $excerpt;
	var $price;
	var $img_count;

	function __construct( $id = null ) {
		if ( !empty( $id ) )
			$this->populate( $id );
	}
	
	public function populate( $id ) {
		global $wpdb, $cp;
		
		$sql = $wpdb->prepare( "SELECT * FROM {$cp->posts->table_name} WHERE id = %d", $id );
		if ( $field = $wpdb->get_row( $sql ) ) {
			
			$this->id             = $field->id;
			$this->parent         = $field->parent;
			$this->author         = $field->author;
			$this->thumb          = $field->thumb;
			$this->date_created   = $field->date_created;
			$this->name           = $field->name;
			$this->excerpt        = $field->excerpt;
			$this->price          = $filed->price;
			$this->img_count      = $filed->img_count;
		}
	}

	public function save() {
		global $wpdb, $cp;

		if ( $this->exists() )
			$sql_cmd = $wpdb->prepare( "UPDATE {$cp->posts->table_name} SET parent = %d, author = %d, thumb = %s, date_created = %d, name = %s, excerpt = %s, price = %d, img_count = %d WHERE id = %d", $this->parent, $this->author, $this->thumb, cp_core_current_time(), $this->name, $this->excerpt, $this->price, $this->img_count, $this->id );
		else
			$sql_cmd = $wpdb->prepare( "INSERT INTO {$cp->posts->table_name} (parent, author, thumb = %s, date_created, name, excerpt, price, img_count) VALUES (%d, %d, %s, %s, %s, %d, %d)", $this->parent, $this->author, $this->thumb, cp_core_current_time(), $this->name, $this->excerpt, $this->price, $this->img_count );


		if ( false === $wpdb->query($sql_cmd) )
			return false;

		if ( empty( $this->id ) )
			$this->id = $wpdb->insert_id;

		wp_cache_delete( 'cp_posts_' . $this->id, 'cp' );

		return true;
	}
		
	public function delete( $id = null ) {
		global $wpdb, $ppy;
		
		if ( empty( $id ) )
			$id = $this->id;
		
		wp_cache_delete( 'cp_posts_' . $this->id, 'cp' );

		$sql_cmd = $wpdb->prepare( "DELETE FROM {$cp->post->table_name} WHERE id = %d", $id );
		if ( !$wpdb->query( $sql_cmd ) )
			return false;
		
		return true;
	}
		
	/** Static Methods ****************************************************/
	
	/**
	* Get whether a post exists for a given slug.
	*
	* @param string $slug Slug to check.
	* @param string $table_name Optional. Name of the table to check
	*        against. Default: $bp->groups->table_name.
	* @return string|null ID of the group, if one is found, else null.
	*/
	public static function post_exists( $post_id ) {
		global $wpdb, $cp;
				
		if ( empty( $slug ) )
			return false;
			
		$retval = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$cp->posts->table_name} WHERE id = %d", $post_id ) );
		
		return $retval;
	}
	
	/**
	* Get the ID of a post by the post's slug.
	*
	* Alias of {@link CP_Post::post_exists()}.
	*
	* @param string $slug See {@link CP_Post::post_exists()}.
	* @return string|null See {@link CP_Post::post_exists()}.
	*/
	public static function get_id_from_slug( $slug ) {
		return CP_Post::post_exists( $slug );
	}
	
	public function delete_for_user( $author ) {
		global $wpdb, $ppy;
	
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$cp->posts->table_name} WHERE author = %d", $author ) );
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
		$order = $orderby = '';
	
		switch ( $type ) {
			case 'newest' :
				$order   = 'DESC';
				$orderby = 'date_created';
				break;
		
			case 'alphabetical' :
				$order   = 'ASC';
				$orderby = 'name';
				break;
	
			case 'random' :
				$order   = '';
				$orderby = 'random';
				break;
		}
	
		return array( 'order' => $order, 'orderby' => $orderby );
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
				$order_by_term = 'p.date_created';
				break;
					
			case 'name' :
				$order_by_term = 'p.name';
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
				'type'            => null,
				'orderby'         => 'date_created',
				'order'           => 'DESC',
				'per_page'        => null,
				'page'            => null,
				'parent'          => 0,
				'user_id'         => 0,
				'search_terms'    => false,
				'meta_query'      => false
		);
		
		$r = wp_parse_args( $args, $defaults );
		
		$sql       = array();
		$tables    = array();
		$clause    = array();
		
		$sql['select'] = "SELECT DISTINCT p.* ";
		
		$tables[]   = "FROM {$cp->posts->table_name} p ";
		if ( !empty( $r['meta_query'] ) || !empty( $r['search_terms'] ) ) 
			$tables[] = "JOIN {$cp->post->table_name_postmeta} pm ON p.id = pm.post_id";
		
		if ( !empty( $r['user_id'] ) ) 
			$clause[] = $wpdb->prepare( " p.author = %d", $r['user_id'] );
		
		if ( !empty( $r['parent'] ) )
			$clause[] = $wpdb->prepare( " p.parent = %d", $r['parent'] );
		
		if ( ! empty( $r['search_terms'] ) ) {
			$r['search_terms'] = esc_sql( like_escape( $r['search_terms'] ) );
			$sql['search'] = " AND ( p.name LIKE '%%{$r['search_terms']}%%' OR p.excerpt LIKE '%%{$r['search_terms']}%%' )";
		}
		
		if ( !empty( $r['meta_query'] ) ) {
			$posts_meta_query = new WP_Meta_Query( $r['meta_query'] );
			
			$wpdb->groupmeta = $cp->post->table_name_postmeta;
			
			$meta_sql = $posts_meta_query->get_sql( 'post', 'p', 'id' );
		}
		$sql['from']  = join( ' ', $tables );
		$sql['where'] = join( ' AND ', (array) $clause );
		if ( !empty( $sql['where'] ) )
			$sql['where'] = 'WHERE ' . $sql['where'];
		
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
		$order = cp_esc_sql_order( $order );
		
		// Convert 'orderby' into the proper ORDER BY term
		$orderby = self::convert_orderby_to_order_by_term( $orderby );
		
		// Random order is a special case
		if ( 'rand()' === $orderby ) {
			$sql['order'] = "ORDER BY rand()";
		} else {
			$sql['order'] = "ORDER BY {$orderby} {$order}";
		}
		
		if ( ! empty( $r['per_page'] ) && ! empty( $r['page'] ) ) {
			$sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $r['page'] - 1 ) * $r['per_page']), intval( $r['per_page'] ) );
		}
		
		
		// Get paginated results
		$paged_posts_sql = apply_filters( 'cp_posts_get_paged_posts_sql', join( ' ', (array) $sql ), $sql, $r );
		$paged_posts     = $wpdb->get_results( $paged_posts_sql );
		
		
		$sql['select'] = "SELECT COUNT(DISTINCT p.id)";
		$total_sql     = apply_filters( 'cp_posts_get_total_posts_sql', join( ' ', (array) $sql ), $sql, $r );
		$total_posts  = $wpdb->get_var( $total_sql );
		
		unset( $sql, $total_sql );
		return array( 'posts' => $paged_posts, 'total' => $total_posts );
	}
}