<?php
/**
 * Hide admin top bar
 */
add_filter('show_admin_bar', '__return_false');

/*
 * Remove the wordpress upgrade check in Admin
 * to avoid an upgrade outside our development cycle
 */
function remove_core_updates() {
	global $wp_version;

	return (object) array(
			'last_checked'=> time(),
			'version_checked'=> $wp_version,
			'updates' => array()
		);
}

add_filter('pre_site_transient_update_core','remove_core_updates');
add_filter('pre_site_transient_update_plugins','remove_core_updates');
add_filter('pre_site_transient_update_themes','remove_core_updates');

/**
 * Add custom stylesheet to TinyMCE editor
 * https://codex.wordpress.org/Function_Reference/add_editor_style
 */
add_action('admin_init', function() {
	add_editor_style('editor-styles.css');
});

/**
 * Hide menu items for non-administrators
 */
add_action('admin_menu', function() {
	if (current_user_can('administrator')) {
		return;
	}

	remove_menu_page('edit.php?post_type=acf-field-group');
	remove_menu_page('edit-comments.php');
	remove_menu_page('options-general.php');
	remove_menu_page('plugins.php');
	remove_menu_page('themes.php');
	remove_menu_page('tools.php');
});

/**
 * Add options page
 */
if (function_exists('acf_add_options_page')) {
	acf_add_options_page();
}
