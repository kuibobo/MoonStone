<?php

if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_admin_settings() {
	
	$form_action = add_query_arg( 'page', 'cp-settings', bp_get_admin_url( 'admin.php' ) );
?>
	<div class="wrap">

		<?php screen_icon( 'categorypress' ); ?>

		<h2 class="nav-tab-wrapper"><?php cp_core_admin_tabs( __( 'Settings', 'categorypress' ) ); ?></h2>

		<form action="<?php echo $form_action ?>" method="post">

			<?php settings_fields( 'categorypress' ); ?>

			<?php do_settings_sections( 'categorypress' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'categorypress' ); ?>" />
			</p>
		</form>
	</div>
<?php
}

function cp_core_admin_settings_save() {
	if ( isset( $_GET['page'] ) && 'cp-settings' == $_GET['page'] && !empty( $_POST['submit'] ) ) {
		check_admin_referer( 'categorypress-options' );
		
		global $wp_settings_fields;

		if ( isset( $wp_settings_fields['categorypress'] ) ) {
			foreach( (array) $wp_settings_fields['categorypress'] as $section => $settings ) {
				foreach( $settings as $setting_name => $setting ) {
					$value = isset( $_POST[$setting_name] ) ? $_POST[$setting_name] : '';

					cp_update_option( $setting_name, $value );
				}
			}
		}
		
		cp_core_redirect( add_query_arg( array( 'page' => 'cp-settings', 'updated' => 'true' ), cp_get_admin_url( 'admin.php' ) ) );
	}
}
add_action( 'cp_admin_init', 'cp_core_admin_settings_save', 100 );

function cp_core_admin_settings_handler() {
}
add_action( 'cp_admin_init', 'cp_core_admin_settings_handler' );

function cp_admin_setting_callback_main_section() {
}

function cp_admin_setting_callback_meta_ed2k_settings() {
?>
	<input id="cp-meta-ed2k-name" name="cp-meta-ed2k-name" type="text" value="<?php echo cp_get_option( 'cp-meta-ed2k-name', 'ed2k' ); ?>"/>
	<label for="cp-meta-ed2k-name"><?php _e( 'Show ed2k name', 'buddypress' ); ?></label>
	<p class="description"><?php _e( 'Show ed2k name.', 'buddypress' ); ?></p>
<?php
}

function cp_admin_setting_callback_meta_maget_settings() {
?>
	<input id="cp-meta-magnet-name" name="cp-meta-magnet-name" type="text" value="<?php echo cp_get_option( 'cp-meta-magnet-name', 'magnet' ); ?>"/>
	<label for="cp-meta-magnet-name"><?php _e( 'Show magnet name', 'buddypress' ); ?></label>
	<p class="description"><?php _e( 'Show magnet name.', 'buddypress' ); ?></p>
<?php
}