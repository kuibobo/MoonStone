<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Include WP's list table class
if ( !class_exists( 'WP_List_Table' ) ) require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class CP_Post_List_Table extends WP_List_Table {
	
	public function __construct() {
		parent::__construct( array(
			'ajax'     => false,
			'plural'   => 'categories',
			'singular' => 'category',
			'screen'   => get_current_screen(),
		) );
	}
	
	function prepare_items() {
		$post_status = 0;
		
		// Set current page
		$page = $this->get_pagenum();

		// Set per page from the screen options
		$per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );
			
		
		if ( !empty( $_REQUEST['post_status'] ) )
			$post_status = $_REQUEST['post_status'];
			
		// Get the categories from the database
		$datas = cp_posts_get_posts( array( 
						'status'         => $post_status,
						'page'           => $page,
						'per_page'       => $per_page
					) );
													
		// If we're viewing a specific category, flatten all activites into a single array.
		if ( $include_id ) {
			$datas['posts'] = CP_Posts_List_Table::flatten_category_array( $datas['posts'] );
			$datas['total']      = count( $categories['posts'] );

			// Sort the array by the category object's date_recorded value
			usort( $datas['posts'], create_function( '$a, $b', 'return $a->date_recorded > $b->date_recorded;' ) );
		}

		// bp_category_get returns an array of objects; cast these to arrays for WP_List_Table.
		$new_posts = array();
		foreach ( $datas['posts'] as $post_item ) {
			$new_posts[] = (array) $post_item;

			// Build an array of category-to-user ID mappings for better efficency in the In Response To column
			$this->post_user_id[$post_item->id] = $post_item->user_id;
		}

		// Set raw data to display
		$this->items       = $new_posts;

		// Store information needed for handling table pagination
		$this->set_pagination_args( array(
			'per_page'    => $per_page,
			'total_items' => $datas['total'],
			'total_pages' => ceil( $datas['total'] / $per_page )
		) );

		// Don't truncate category items; cp_post_truncate_entry() needs to be used inside a BP_category_Template loop.
		remove_filter( 'cp_get_post_content_body', 'cp_post_truncate_entry', 5 );
	}
	
	function display() {
		extract( $this->_args );

		$this->display_tablenav( 'top' ); ?>

		<table class="<?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>

			<tbody id="the-comment-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php

		$this->display_tablenav( 'bottom' );
	}
	
	function single_row( $item ) {
		static $even = false;

		if ( $even ) {
			$row_class = ' class="even"';
		} else {
			$row_class = ' class="alternate odd"';
		}

		$root_id = $item['id'];

		echo '<tr' . $row_class . ' id="post-' . esc_attr( $item['id'] ) . '" data-parent_id="' . esc_attr( $item['id'] ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
		echo $this->single_row_columns( $item );
		echo '</tr>';

		$even = ! $even;
	}
	
	function get_bulk_actions() {
		$actions = array();
		
		//$actions['bulk_delete'] = __( 'Delete', 'categorypress' );
		
		$post_status  = cp_posts_get_status();
		
		foreach ( $post_status as $k => $v ) 
			$actions[$k] = esc_html( $v );
			
		return $actions;
	}
	
	function get_columns() {
		return array(
			'cb'       => '<input name type="checkbox" />',
			'name'     => __( 'Name', 'categorypress' ),
			'status'   => __( 'Status', 'categorypress' )
		);
	}
	
	function get_sortable_columns() {
		return array();
	}
	
	function extra_tablenav( $which ) {
		if ( 'bottom' == $which )
			return;

		$selected = !empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : '';

		// Get all types of activities, and sort alphabetically.
		$post_status  = cp_posts_get_status();
		//natsort( $status );
	?>

		<div class="alignleft status">
			<select name="post_status">
				<option value="" <?php selected( !$selected ); ?>><?php _e( 'Show all posts', 'categorypress' ); ?></option>

				<?php foreach ( $post_status as $k => $v ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k,  $selected ); ?>><?php echo esc_html( $v ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php submit_button( __( 'Filter', 'categorypress' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) ); ?>
		</div>

	<?php
	}
	
	function column_cb( $item ) {
		printf( '<label class="screen-reader-text" for="aid-%1$d">' . __( 'Select activity item %1$d', 'categorypress' ) . '</label><input type="checkbox" name="pid[]" value="%1$d" id="aid-%1$d" />', $item['id'] );
	}
	
	function column_name( $item ) {
		// Preorder items: Reply | Edit | Spam | Delete Permanently
		$actions = array(
			'view'   => '',
			'edit'   => '',
			'delete' => ''
		);

		// Build actions URLs
		$base_url   = cp_get_admin_url( 'admin.php?page=cp-posts&amp;pid=' . $item['id'] );
		$spam_nonce = esc_html( '_wpnonce=' . wp_create_nonce( 'spam-post_' . $item['id'] ) );

		
		$view_url   = $base_url . '&amp;action=view';
		$edit_url   = $base_url . '&amp;action=edit';
		$delete_url = $base_url . "&amp;action=delete&amp;$spam_nonce";

		// Rollover actions
		$actions['view'] = sprintf( '<a href="%s">%s</a>', $view_url, __( 'View', 'categorypress' ) );
		$actions['edit'] = sprintf( '<a href="%s">%s</a>', $edit_url, __( 'Edit', 'categorypress' ) );
		//$actions['delete'] = sprintf( '<a href="%s">%s</a>', $delete_url, __( 'Delete', 'categorypress' ) );
		$actions['delete'] = sprintf( '<a href="%s" onclick="%s">%s</a>', $delete_url, "javascript:return confirm('" . esc_js( __( 'Are you sure?', 'categorypress' ) ) . "'); ", __( 'Delete', 'categorypress' ) );

		// Start timestamp
		echo '<div class="submitted-on">';

		/* translators: 2: category admin ui date/time */
		printf( __( '<a href="%1$s">%2$s</a>', 'categorypress' ), '', $item['name'] );

		// End timestamp
		echo '</div>';

		echo $this->row_actions( $actions );
	}
	
	function column_status( $item ) {
		$status = cp_posts_get_status();
		
		echo $status[$item['status']];
	}
}

/**
 * Register the Post component admin screen.
 *
 * @since CategoryPress (1.1)
 */
function cp_posts_add_admin_menu() {
	
	// Add our screen
	$hook = add_submenu_page(
			'cp-general-settings',
			__( 'Posts', 'categorypress' ),
			__( 'Posts', 'categorypress' ),
			'administrator',
			'cp-posts',
			'cp_posts_admin'
			);
	
	// Hook into early actions to load custom CSS and our init handler.
	add_action( "load-$hook", 'cp_posts_admin_load' );
}
add_action( cp_core_admin_hook(), 'cp_posts_add_admin_menu' );

function cp_posts_admin_load() {
	global $cp_post_list_table;

	$cp_post_list_table = new CP_Post_List_Table();
	
	$doaction = cp_admin_list_table_current_bulk_action();
	
	if ( !empty( $doaction ) && ! in_array( $doaction, array( '-1' ) ) ) {
		
		// Build redirection URL
		$redirect_to = remove_query_arg( array( 'pid', 'error' ), wp_get_referer() );
		$redirect_to = add_query_arg( 'paged', $cp_post_list_table->get_pagenum(), $redirect_to );
		
		$post_ids = array_map( 'absint', (array) $_REQUEST['pid'] );
		
		if ( 'bulk_' == substr( $doaction, 0, 5 ) && ! empty( $_REQUEST['pid'] ) ) {
			// Check this is a valid form submission
			check_admin_referer( 'bulk-activities' );

			// Trim 'bulk_' off the action name to avoid duplicating a ton of code
			$new_status = substr( $doaction, 5 );
		} 
		
		$errors = array();
		
		foreach ( $post_ids as $post_id ) {
			cp_posts_update_status( $post_id, $statu );
		}
		
		$redirect_to = add_query_arg( 'updated', $deleted, $redirect_to );
		
		wp_redirect( $redirect_to );
		exit;
		
	} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
		wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}
}

function cp_posts_admin() {
	// Decide whether to load the index or edit screen
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	// Display the single category edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['cid'] ) )
		cp_posts_admin_edit();
	
	// Otherwise, display the category index screen
	else
		cp_posts_admin_index();
}

function cp_posts_admin_edit() {
}

function cp_posts_admin_index() {
	global $cp_post_list_table, $plugin_page;
	
	$cp_post_list_table->prepare_items();
?>
	<div class="wrap nosubsub">
		<?php screen_icon( 'categorypress-groups' ); ?>
		<h2>
			<?php _e( 'Posts', 'categorypress' ); ?>

			<?php if ( !empty( $_REQUEST['s'] ) ) : ?>
				<span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'categorypress' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
			<?php endif; ?>
		</h2>
		
		<?php // If the user has just made a change to an group, display the status messages ?>
		<?php if ( !empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_REQUEST['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>

		<?php // Display each group on its own row ?>
		<?php $cp_post_list_table->views(); ?>

		<form id="bp-groups-form" action="" method="get">
			<?php $cp_post_list_table->search_box( __( 'Search all Posts', 'categorypress' ), 'bp-groups' ); ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
			<?php $cp_post_list_table->display(); ?>
		</form>
	</div>
<?php
}