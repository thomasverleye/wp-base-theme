<?php

function clean_wp_output() {
	// Emoji
	function disable_tinymce_emojicons($plugins) {
		if (is_array($plugins)) {
			return array_diff($plugins, array('wpemoji'));
		}

		return array();
	}

	// WP Embed / oEmbed
    remove_action('rest_api_init', 'wp_oembed_register_route');
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');

	// Front-end
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('embed_head', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('wp_head', 'rest_output_link_wp_head');
	remove_action('template_redirect', 'rest_output_link_header', 11, 0);

	// TinyMCE
	add_filter('tiny_mce_plugins', 'disable_tinymce_emojicons');

	// Other
	remove_action('wp_mail', 'wp_staticize_emoji_for_email');
	remove_action('the_content_feed', 'wp_staticize_emoji');
	remove_action('comment_text_rss', 'wp_staticize_emoji');

	// Header bloat
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
	remove_action('wp_head', 'wp_generator');
}

add_action('init', 'clean_wp_output');
