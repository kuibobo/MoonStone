<?php

if ( !defined( 'ABSPATH' ) ) exit;
/**
 * Fire 'bp_head', which is used to hook scripts and styles in the <head>.
 *
 * Hooked to 'wp_head'.
 */
function cp_head() {
	do_action ( 'cp_head' );
}

function cp_actions() {
	do_action( 'cp_actions' );
}

function cp_screens() {
	do_action( 'cp_screens' );
}

function cp_include() {
	do_action( 'cp_include' );
}

function cp_init() {
	do_action( 'cp_init' );
}

function cp_template_redirect() {
	do_action( 'cp_template_redirect' );
}

function cp_loaded() {
	do_action( 'cp_loaded' );
}

function cp_setup_components() {
	do_action( 'cp_setup_components' );
}

function cp_setup_globals() {
	do_action( 'cp_setup_globals' );
}

function cp_setup_nav() {
	do_action( 'cp_setup_nav' );
}