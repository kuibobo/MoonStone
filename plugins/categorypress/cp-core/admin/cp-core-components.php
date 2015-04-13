<?php

/**
 * BuddyPress Admin Component Functions
 *
 * @package BuddyPress
 * @subpackage CoreAdministration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_admin_components_settings() {
?>
	<div class="wrap">
		<?php screen_icon( 'categorypress'); ?>

		<h2 class="nav-tab-wrapper"><?php cp_core_admin_tabs( __( 'Components', 'categorypress' ) ); ?></h2>
		<form action="" method="post" id="cp-admin-component-form">
		
		</form>
	</div>
<?php
}

function cp_core_admin_components_settings_handler() {
}
add_action( 'cp_admin_init', 'cp_core_admin_components_settings_handler' );