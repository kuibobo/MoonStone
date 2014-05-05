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

/**
 * Output the Activity component admin screens.
 *
 * @since CategoryPress (1.6.0)
 */
function cp_category_admin() {
	// Decide whether to load the index or edit screen
	$doaction = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	// Display the single activity edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['aid'] ) )
		cp_activity_admin_edit();
	
	// Otherwise, display the Activity index screen
	else
		cp_activity_admin_index();
}

function cp_category_admin_load() {
	// Decide whether to load the dev version of the CSS and JavaScript
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : 'min.';

	// Edit screen
	if ( 'edit' == $doaction && ! empty( $_GET['aid'] ) ) {
	
	} else {
	
	}
}

function cp_activity_admin_index() {
?>
	<div class="wrap">
		<h2>
			<?php if ( !empty( $_REQUEST['aid'] ) ) : ?>
				<?php printf( __( 'Category related to ID #%s', 'categorypress' ), number_format_i18n( (int) $_REQUEST['aid'] ) ); ?>
			<?php else : ?>
				<?php _e( 'Category', 'categorypress' ); ?>
			<?php endif; ?>

			<?php if ( !empty( $_REQUEST['s'] ) ) : ?>
				<span class="subtitle"><?php printf( __( 'Search results for &#8220;%s&#8221;', 'categorypress' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
			<?php endif; ?>
		</h2>
		
		<?php // If the user has just made a change to an activity item, display the status messages ?>
		<?php if ( !empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_REQUEST['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>
		
		<form id="cp-categories-form" action="" method="get">

			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />

		</form>
		
		<table class="widefat fixed categories" cellspacing="0">
			<thead><tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox" /></th>
				<th scope='col' id='name' class='manage-column column-name'>Name</th>
				<th scope='col' id='comment' class='manage-column column-comment'>-</th>
				<th scope='col' id='response' class='manage-column column-response'>In Response To</th>
			</tr></thead>
			
			<tfoot><tr>
				<th scope='col' class='manage-column column-cb check-column'><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox" /></th>
				<th scope='col'  class='manage-column column-author'>Author</th>
				<th scope='col'  class='manage-column column-comment'>Activity</th>
				<th scope='col'  class='manage-column column-response'>In Response To</th>
			</tr></tfoot>
			
			<tbody id="the-comment-list">
				<tr class="alternate odd" id="activity-3461" data-parent_id="3461" data-root_id="3461">
					<th scope="row" class="check-column"><label class="screen-reader-text" for="cid-3461">Select category item 3461</label><input type="checkbox" name="aid[]" value="3461" id="aid-3461" /></th>
					<td class='author column-author'></td>
					<td class='comment column-comment'></td>
					<td class='response column-response'></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
}