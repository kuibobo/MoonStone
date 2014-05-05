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
		
		<?php // This markup is used for the reply form ?>
		<table style="display: none;">
			<tr id="bp-activities-container" style="display: none;">
				<td colspan="4">
					<form method="get" action="">

						<h5 id="bp-replyhead"><?php _e( 'Reply to Category', 'categorypress' ); ?></h5>
						<?php wp_editor( '', 'cp-categories', array( 'dfw' => false, 'media_buttons' => false, 'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,code,spell,close' ), 'tinymce' => false, ) ); ?>

						<p id="bp-replysubmit" class="submit">
							<a href="#" class="cancel button-secondary alignleft"><?php _e( 'Cancel', 'categorypress' ); ?></a>
							<a href="#" class="save button-primary alignright"><?php _e( 'Reply', 'categorypress' ); ?></a>

							<img class="waiting" style="display:none;" src="<?php echo esc_url( bp_get_admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
							<span class="error" style="display:none;"></span>
							<br class="clear" />
						</p>

						<?php wp_nonce_field( 'cp-category-admin-reply', '_ajax_nonce-cp-category-admin-reply', false ); ?>

					</form>
				</td>
			</tr>
		</table>
		
	</div>
<?php
}