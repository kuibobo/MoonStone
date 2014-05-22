<?php

if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_admin_slugs_settings() {
?>
	<div class="wrap">
		<?php screen_icon( 'categorypress'); ?>

		<h2 class="nav-tab-wrapper"><?php cp_core_admin_tabs( __( 'Pages', 'categorypress' ) ); ?></h2>
		<form action="" method="post" id="cp-admin-component-form">
			
			<?php cp_core_admin_slugs_options(); ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="cp-admin-pages-submit" id="cp-admin-pages-submit" value="<?php esc_attr_e( 'Save Settings', 'categorypress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'cp-admin-pages-setup' ); ?>
			
		</form>
	</div>
<?php
}

function cp_core_admin_slugs_options() {
	global $cp;

	// Get the existing WP pages
	$existing_pages = cp_core_get_directory_page_ids();

	// Set up an array of components (along with component names) that have
	// directory pages.
	$directory_pages = array();
	
	if ( is_array( $cp->loaded_components ) ) {
		foreach( $cp->loaded_components as $component_slug => $component_id ) {

			// Only components that need directories should be listed here
			if ( isset( $cp->{$component_id} ) && !empty( $cp->{$component_id}->has_directory ) ) 
				$directory_pages[$component_id] = !empty( $cp->{$component_id}->name ) ? $cp->{$component_id}->name : ucwords( $component_id );
			
		}
	}
	
	if ( !empty( $directory_pages ) ) : ?>
		<h3><?php _e( 'Directories', 'categorypress' ); ?></h3>

		<p><?php _e( 'Associate a WordPress Page with each CategoryPress component directory.', 'categorypress' ); ?></p>
		
		<table class="form-table">
			<tbody>

				<?php foreach ( $directory_pages as $name => $label ) : ?>

					<tr valign="top">
						<th scope="row">
							<label for="cp_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
						</th>

						<td>

							<?php if ( ! cp_is_root_blog() ) switch_to_blog( cp_get_root_blog_id() ); ?>

							<?php echo wp_dropdown_pages( array(
								'name'             => 'cp_pages[' . esc_attr( $name ) . ']',
								'echo'             => false,
								'show_option_none' => __( '- None -', 'categorypress' ),
								'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
							) ); ?>

							<a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) ); ?>" class="button-secondary"><?php _e( 'New Page', 'categorypress' ); ?></a>
							<input class="button-primary" type="submit" name="cp-admin-pages-single" value="<?php esc_attr_e( 'Save', 'categorypress' ) ?>" />

							<?php if ( !empty( $existing_pages[$name] ) ) : ?>

								<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_bp"><?php _e( 'View', 'categorypress' ); ?></a>

							<?php endif; ?>

							<?php if ( ! cp_is_root_blog() ) restore_current_blog(); ?>

						</td>
					</tr>


				<?php endforeach ?>

				<?php do_action( 'cp_active_external_directories' ); ?>

			</tbody>
		</table>
	<?php
	
	endif;
}

function cp_core_admin_slugs_setup_handler() {
	if ( isset( $_POST['cp-admin-pages-submit'] ) || isset( $_POST['cp-admin-pages-single'] ) ) {
		if ( !check_admin_referer( 'cp-admin-pages-setup' ) )
			return false;

		// Then, update the directory pages
		if ( isset( $_POST['cp_pages'] ) ) {

			$directory_pages = array();

			foreach ( (array) $_POST['cp_pages'] as $key => $value ) {
				if ( !empty( $value ) ) {
					$directory_pages[$key] = (int) $value;
				}
			}
			cp_core_update_directory_page_ids( $directory_pages );
		}

		$base_url = cp_get_admin_url( add_query_arg( array( 'page' => 'cp-page-settings', 'updated' => 'true' ), 'admin.php' ) );

		wp_redirect( $base_url );
	}
}
add_action( 'cp_admin_init', 'cp_core_admin_slugs_setup_handler' );