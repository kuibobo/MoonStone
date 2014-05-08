<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the Category component admin screen.
 *
 * @since CategoryPress (1.1)
 */
function cp_category_add_admin_menu() {
	
	// Add our screen
	$hook = add_menu_page(
			__( 'Category', 'categorypress' ),
			__( 'Category', 'categorypress' ),
			'administrator',
			'cp-category',
			'cp_category_admin',
			'div'
			);
	
	
	// Hook into early actions to load custom CSS and our init handler.
	add_action( "load-$hook", 'cp_category_admin_load' );
}
add_action( cp_core_admin_hook(), 'cp_category_add_admin_menu' );

function cp_category_admin_load() {
	global $cp_category_list_table;
	
	// Decide whether to load the dev version of the CSS and JavaScript
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

	// Build redirection URL
	$redirect_to = remove_query_arg( array( 'action', 'cid', 'deleted', 'error' ), $_SERVER['REQUEST_URI'] );

	$doaction = cp_admin_list_table_current_bulk_action();

	// Edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['cid'] ) ) {
	
	} else if ( 'save' == $doaction ) {
		// Get activity ID
		$category_id = (int) $_REQUEST['cid'];

		// Check this is a valid form submission
		check_admin_referer( 'edit-category_' . $category_id );

		// Get the activity from the database
		$category = cp_categories_get_category( array( 'id' => $category_id ) );
		
		if ( isset( $_POST['cp-category-type'] ) )
			$category->type = $_POST['cp-category-type'];
			
		if ( isset( $_POST['cp-category-name'] ) )
			$category->name = $_POST['cp-category-name'];
			
		if ( isset( $_POST['cp-category-slug'] ) )
			$category->slug = $_POST['cp-category-slug'];
			
		if ( isset( $_POST['cp-category-description'] ) )
			$category->description = $_POST['cp-category-description'];
			
		// Save
		$result = $category->save();
		
		if ( false === $result )
			$error = $activity->id;
			
		if ( $error )
			$redirect_to = add_query_arg( 'error', (int) $error, $redirect_to );
		else
			$redirect_to = add_query_arg( 'updated', (int) $category->id, $redirect_to );
			
		wp_redirect( $redirect_to );
		exit;
	} else {
		$cp_category_list_table = new CP_Categories_List_Table();
	}
}

class CP_Categories_List_Table extends WP_List_Table {
	
	public function __construct() {

		// Define singular and plural labels, as well as whether we support AJAX.
		parent::__construct( array(
			'ajax'     => false,
			'plural'   => 'categories',
			'singular' => 'category',
			'screen'   => get_current_screen(),
		) );
	}
	
	function prepare_items() {
		// Option defaults
		$filter           = array();
		$include_id       = false;
		$search_terms     = false;
		$sort             = 'DESC';

		// Set current page
		$page = $this->get_pagenum();

		// Set per page from the screen options
		$per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );

		// Sort order
		if ( !empty( $_REQUEST['order'] ) && 'desc' != $_REQUEST['order'] )
			$sort = 'ASC';
			
		// Filter
		if ( !empty( $_REQUEST['category_type'] ) )
			$filter = array( 'action' => $_REQUEST['category_type'] );

		// Are we doing a search?
		if ( !empty( $_REQUEST['s'] ) )
			$search_terms = $_REQUEST['s'];

		// Check if user has clicked on a specific category (if so, fetch only that, and any related, category).
		if ( !empty( $_REQUEST['cid'] ) )
			$include_id = (int) $_REQUEST['cid'];

		// Get the categories from the database
		$categories = cp_categories_get_categories( array( 
														'parent_id'      => $parent_id,
														'page'           => $page_index
													) );

		// If we're viewing a specific category, flatten all activites into a single array.
		if ( $include_id ) {
			$categories['categories'] = CP_Categories_List_Table::flatten_category_array( $categories['categories'] );
			$categories['total']      = count( $categories['categories'] );

			// Sort the array by the category object's date_recorded value
			usort( $categories['categories'], create_function( '$a, $b', 'return $a->date_recorded > $b->date_recorded;' ) );
		}

		// bp_category_get returns an array of objects; cast these to arrays for WP_List_Table.
		$new_categories = array();
		foreach ( $categories['categories'] as $category_item ) {
			$new_categories[] = (array) $category_item;

			// Build an array of category-to-user ID mappings for better efficency in the In Response To column
			$this->category_user_id[$category_item->id] = $category_item->user_id;
		}

		// Set raw data to display
		$this->items       = $new_categories;

		// Store information needed for handling table pagination
		$this->set_pagination_args( array(
			'per_page'    => $per_page,
			'total_items' => $categories['total'],
			'total_pages' => ceil( $categories['total'] / $per_page )
		) );

		// Don't truncate category items; bp_category_truncate_entry() needs to be used inside a BP_category_Template loop.
		remove_filter( 'cp_get_category_content_body', 'cp_category_truncate_entry', 5 );
	}
	
	/**
	 * Output the Category data table.
	 *
	 * @since BuddyPress (1.6.0)
	*/
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

		echo '<tr' . $row_class . ' id="category-' . esc_attr( $item['id'] ) . '" data-parent_id="' . esc_attr( $item['id'] ) . '" data-root_id="' . esc_attr( $root_id ) . '">';
		echo $this->single_row_columns( $item );
		echo '</tr>';

		$even = ! $even;
	}
	
	function get_bulk_actions() {
		$actions = array();
		
		$actions['bulk_delete'] = __( 'Delete', 'categorypress' );

		return $actions;
	}

	function get_columns() {
		return array(
			'cb'       => '<input name type="checkbox" />',
			'name'   => __( 'Name', 'categorypress' ),
			'slug'  => __( 'Slug', 'categorypress' ),
			'description' => __( 'Description', 'categorypress' ),
		);
	}

	function get_sortable_columns() {
		return array();
	}

	function extra_tablenav( $which ) {
		if ( 'bottom' == $which )
			return;

		$selected = !empty( $_REQUEST['category_type'] ) ? $_REQUEST['category_type'] : '';

		// Get all types of activities, and sort alphabetically.
		$actions  = cp_categories_get_types();
		//natsort( $actions );
	?>

		<div class="alignleft actions">
			<select name="category_type">
				<option value="" <?php selected( !$selected ); ?>><?php _e( 'Show all category types', 'categorypress' ); ?></option>

				<?php foreach ( $actions as $k => $v ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k,  $selected ); ?>><?php echo esc_html( $v ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php submit_button( __( 'Filter', 'categorypress' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) ); ?>
		</div>

	<?php
	}


	function column_cb( $item ) {
		printf( '<label class="screen-reader-text" for="cid-%1$d">' . __( 'Select category item %1$d', 'categorypress' ) . '</label><input type="checkbox" name="cid[]" value="%1$d" id="cid-%1$d" />', $item['id'] );
	}

	function column_slug( $item ) {
		echo '<strong>' . $item['slug'] . '</strong>';
	}


	function column_name( $item ) {
		// Determine what type of item (row) we're dealing with
		if ( $item['is_spam'] )
			$item_status = 'spam';
		else
			$item_status = 'all';

		// Preorder items: Reply | Edit | Spam | Delete Permanently
		$actions = array(
			'view'   => '',
			'edit'   => '',
			'delete' => ''
		);

		// Build actions URLs
		$base_url   = cp_get_admin_url( 'admin.php?page=cp-category&amp;cid=' . $item['id'] );
		$spam_nonce = esc_html( '_wpnonce=' . wp_create_nonce( 'spam-category_' . $item['id'] ) );

		
		$view_url   = $base_url . '&amp;action=view';
		$edit_url   = $base_url . '&amp;action=edit';
		$delete_url = $base_url . "&amp;action=delete&amp;$spam_nonce";

		// Rollover actions
		$actions['view'] = sprintf( '<a href="%s">%s</a>', $view_url, __( 'View', 'categorypress' ) );
		$actions['edit'] = sprintf( '<a href="%s">%s</a>', $edit_url, __( 'Edit', 'categorypress' ) );
		$actions['delete'] = sprintf( '<a href="%s">%s</a>', $delete_url, __( 'Delete', 'categorypress' ) );
		
		// Start timestamp
		echo '<div class="submitted-on">';

		/* translators: 2: category admin ui date/time */
		printf( __( '<a href="%1$s">%2$s</a>', 'categorypress' ), '', $item['name'] );

		// End timestamp
		echo '</div>';

		echo $this->row_actions( $actions );
	}

	function column_description( $item ) {
		echo $item['description']; 
	}
}

/**
 * Output the category component admin screens.
 *
 * @since CategoryPress (1.6.0)
 */
function cp_category_admin() {
	// Decide whether to load the index or edit screen
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	// Display the single category edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['cid'] ) )
		cp_category_admin_edit();
	
	// Otherwise, display the category index screen
	else
		cp_category_admin_index();
}


function cp_category_admin_edit() {
	if ( ! is_super_admin() )
		die( '-1' );
	
	$category = cp_categories_get_category( array(
		'id' => ! empty( $_REQUEST['cid'] ) ? (int) $_REQUEST['cid'] : 0,
		) );
		
	$form_url = remove_query_arg( array( 'action', 'deleted', 'error', 'spammed', 'unspammed', ), $_SERVER['REQUEST_URI'] );
	$form_url = add_query_arg( 'action', 'save', $form_url );
	
	$types  = cp_categories_get_types();
?>
	<div class="wrap">
		<?php screen_icon( 'categorypress-activity' ); ?>
		<h2><?php  _e( 'Edit Category', 'categorypress' ) ; ?></h2>
		
		<?php if ( ! empty( $category ) ) : ?>
		
			<form class="validate" action="<?php echo esc_attr( $form_url ); ?>" method="post" id="category_update">
				<table class="form-table">
					<tr class="form-field form-required">
						<th scope="row" valign="top"><label for="name"><?php _ex( 'Name', 'categorypress'); ?></label></th>
						<td><input name="name" id="cp-category-name" type="text" value="<?php if ( isset( $category->name ) ) echo esc_attr($category->name); ?>" size="40" aria-required="true" />
						<p class="description"><?php _e( 'The name is how it appears on your site.', 'categorypress' ); ?></p></td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row" valign="top"><label for="name"><?php _ex( 'Type', 'categorypress'); ?></label></th>
						<td>
						<select name="category_type">
							<option value="" <?php selected( !$category->type ); ?>><?php _e( '-Please Selecte-', 'categorypress' ); ?></option>

							<?php foreach ( $types as $k => $v ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $k,  $category->type ); ?>><?php echo esc_html( $v ); ?></option>
							<?php endforeach; ?>
						</select>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top"><label for="slug"><?php _ex( 'Slug', 'categorypress'); ?></label></th>
						<td><input name="cp-category-slug" id="cp-category-slug" type="text" value="<?php if ( isset( $category->slug ) ) echo esc_attr(apply_filters('editable_slug', $category->slug)); ?>" size="40" />
						<p class="description"><?php _e( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'categorypress' ); ?></p></td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top"><label for="parent"><?php _ex( 'Parent', 'categorypress'); ?></label></th>
						<td><input name="cp-category-parent" id="cp-category-parent" type="text" value="<?php if ( isset( $category->parent_id ) ) echo esc_attr($category->parent_id); ?>" size="40" aria-required="true" />
							<p class="description"><?php _e( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'categorypress' ); ?></p></td>
					</tr>

					<tr class="form-field">
						<th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
						<td><textarea name="cp-category-description" id="cp-category-description" rows="5" cols="50" class="large-text"><?php echo $category->description; // textarea_escaped ?></textarea><br />
						<span class="description"><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></span></td>
					</tr>
				</table>
				<?php wp_nonce_field( 'edit-category_' . $category->id ); ?>
				<?php submit_button( __('Update') );?>
			</form>
			
		<?php endif;?>
	</div>			
<?
}
	
function cp_category_admin_index() {
	global $cp_category_list_table, $plugin_page;
	
	$cp_category_list_table->prepare_items();;
?>
	<div class="wrap nosubsub">
		<h2>
			<?php if ( !empty( $_REQUEST['cid'] ) ) : ?>
				<?php printf( __( 'Category related to ID #%s', 'categorypress' ), number_format_i18n( (int) $_REQUEST['cid'] ) ); ?>
			<?php else : ?>
				<?php _e( 'Categories', 'categorypress' ); ?>
			<?php endif; ?>

			<?php if ( !empty( $_REQUEST['s'] ) ) : ?>
				<span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'categorypress' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
			<?php endif; ?>
		</h2>
		
		<?php // If the user has just made a change to an category item, display the status messages ?>
		<?php if ( !empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_REQUEST['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>
		
		
		
		<div class="col-container">
			<div id="col-right">
				<div class="col-wrap">
					<form id="cp-categories-form" action="" method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
						<?php $cp_category_list_table->search_box( __( 'Search all Category', 'categorypress' ), 'bp-activity' ); ?>
						<?php $cp_category_list_table->display(); ?>
						
					</form>
					<div class="form-wrap">
						<p><?php printf( __( '<strong>Note:</strong><br />Deleting a category does not delete the posts in that category. Instead, posts that were only assigned to the deleted category are set to the category <strong>%s</strong>.' ), apply_filters( 'the_category', get_cat_name( get_option( 'default_category' ) ) ) ) ?></p>
					</div>
				</div>
			</div>
			
			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h3><?php _e( 'Add New Category', 'categorypress' ); ?></h3>
						<form class="validate" action="" method="post" id="category_add">
							<div class="form-field form-required">
								<label for="name"><?php _e( 'Name', 'categorypress' ); ?></label>
								<input type="text" aria-required="true" size="40" value="" id="name" name="name">
								<p><?php _e( 'The name is how it appears on your site.', 'categorypress' ); ?></p>
							</div>
							<div class="form-field">
								<label for="slug"><?php _ex( 'Slug', 'categorypress' ); ?></label>
								<input name="slug" id="slug" type="text" value="" size="40" />
								<p><?php _e( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'categorypress' ); ?></p>
							</div>
							<div class="form-field">
								<label for="slug"><?php _ex( 'Parent', 'categorypress' ); ?></label>
								<input name="slug" id="slug" type="text" value="" size="40" />
								<p><?php _e( 'Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.', 'categorypress' ); ?></p>
							</div>
							<div class="form-field">
								<label for="description"><?php _ex( 'Description', 'categorypress' ); ?></label>
								<textarea name="description" id="description" rows="5" cols="40"></textarea>
								<p><?php _e( 'The description is not prominent by default; however, some themes may show it.', 'categorypress' ); ?></p>
							</div>
							<p class="submit"><input type="submit" value="<?php _e( 'Add New Category', 'categorypress' ); ?>" class="button button-primary" id="submit" name="submit"></p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}